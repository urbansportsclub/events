<?php

namespace OneFit\Events;

use RdKafka\Conf;
use RdKafka\Producer;
use GuzzleHttp\Client;
use RdKafka\KafkaConsumer;
use OneFit\Events\Models\Source;
use OneFit\Events\Models\Message;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use OneFit\Events\Adapters\CacheAdapter;
use Illuminate\Contracts\Events\Dispatcher;
use OneFit\Events\Services\ConsumerService;
use OneFit\Events\Services\ProducerService;
use OneFit\Events\Observers\CreatedObserver;
use OneFit\Events\Observers\DeletedObserver;
use OneFit\Events\Observers\GenericObserver;
use OneFit\Events\Observers\UpdatedObserver;
use FlixTech\AvroSerializer\Objects\RecordSerializer;
use FlixTech\SchemaRegistryApi\Registry\CachedRegistry;
use FlixTech\SchemaRegistryApi\Registry\PromisingRegistry;

/**
 * Class EventsServiceProvider.
 */
class EventsServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->registerProducer();
        $this->registerConsumer();
    }

    /**
     * Bootstrap application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfig();
        $this->registerSerializer();
        $this->registerObservers();
        $this->registerListeners();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            ProducerService::class,
            ConsumerService::class,
        ];
    }

    /**
     * @param  Conf $configuration
     * @return void
     */
    private function setConfiguration(Conf $configuration): void
    {
        // Initial list of Kafka brokers
        $configuration->set('metadata.broker.list', Config::get('events.metadata.broker.list'));

        // Default timeout for network requests
        $configuration->set('socket.timeout.ms', Config::get('events.socket.timeout.ms'));
        $configuration->set('socket.blocking.max.ms', Config::get('events.socket.blocking.max.ms'));

        // Fetch only the topics in use, reduce the bandwidth
        $configuration->set('topic.metadata.refresh.sparse', Config::get('events.topic.metadata.refresh.sparse'));
        $configuration->set('topic.metadata.refresh.interval.ms', Config::get('events.topic.metadata.refresh.interval.ms'));

        // Timeout for broker API version requests
        $configuration->set('api.version.request.timeout.ms', Config::get('events.api.version.request.timeout.ms'));

        // Signal that librdkafka will use to quickly terminate on rd_kafka_destroy()
        pcntl_sigprocmask(SIG_BLOCK, [Config::get('events.internal.termination.signal')]);
        $configuration->set('internal.termination.signal', Config::get('events.internal.termination.signal'));
    }

    /**
     * @return void
     */
    private function registerSerializer(): void
    {
        $this->app->bind(RecordSerializer::class, function ($app) {
            $registry = new CachedRegistry(
                new PromisingRegistry(
                    new Client(['base_uri' => Config::get('events.schemas.registry.base_uri')])
                ),
                new CacheAdapter()
            );

            return new RecordSerializer(
                $registry,
                [
                    // If you want to auto-register missing schemas set this to true
                    RecordSerializer::OPTION_REGISTER_MISSING_SCHEMAS => false,
                    // If you want to auto-register missing subjects set this to true
                    RecordSerializer::OPTION_REGISTER_MISSING_SUBJECTS => false,
                ]
            );
        });
    }

    /**
     * @return void
     */
    private function registerProducer(): void
    {
        $this->app->bind(ProducerService::class, function ($app) {
            $configuration = $app->make(Conf::class);
            $this->setConfiguration($configuration);

            // Local message timeout. This value is only enforced locally and
            // limits the time a produced message waits for successful delivery.
            $configuration->set('message.timeout.ms', Config::get('events.message.timeout.ms'));
            $configuration->set('queue.buffering.max.ms', Config::get('events.queue.buffering.max.ms'));
            // Indicate if the broker should send response/ack to the client
            $configuration->set('request.required.acks', Config::get('events.request.required.acks'));

            $producer = $app->make(Producer::class, ['conf' => $configuration]);

            $serializer = function () {
                return $this->app->make(RecordSerializer::class);
            };

            return new ProducerService(
                $producer,
                $serializer,
                Config::get('events.schemas', []),
                Config::get('events.flush.timeout.ms'),
                Config::get('events.flush.retries')
            );
        });
    }

    /**
     * @return void
     */
    private function registerConsumer(): void
    {
        $this->app->bind(ConsumerService::class, function ($app, array $params = []) {
            $configuration = $app->make(Conf::class);
            $this->setConfiguration($configuration);

            isset($params['group_id']) && $configuration->set('group.id', $params['group_id']);

            // Set where to start consuming messages when there is no initial offset in
            // offset store or the desired offset is out of range.
            // 'smallest': start from the beginning
            $configuration->set('auto.offset.reset', Config::get('events.auto.offset.reset'));
            // Automatically and periodically commit offsets in the background.
            $configuration->set('enable.auto.commit', Config::get('events.enable.auto.commit'));
            // Automatically store offset of last message provided to application.
            $configuration->set('enable.auto.offset.store', Config::get('events.enable.auto.offset.store'));

            $consumer = $app->make(KafkaConsumer::class, ['conf' => $configuration]);

            $serializer = function () {
                return $this->app->make(RecordSerializer::class);
            };

            return new ConsumerService(
                $consumer,
                $app->make(Message::class),
                $serializer,
                Config::get('events.schemas', [])
            );
        });
    }

    /**
     * @return void
     */
    private function registerObservers(): void
    {
        $producers = Config::get('events.producers', []);

        foreach ($producers as $producer => $observers) {
            if (is_array($observers)) {
                array_walk($observers, function (string $topic, string $type, string $producer) {
                    $this->registerGenericObservers($producer, $type, $topic);
                }, $producer);
            }
        }
    }

    /**
     * Setup exact configuration.
     */
    protected function setupConfig()
    {
        $source = realpath(__DIR__.'/../config/events.php');
        $this->publishes([$source => $this->configPath('events.php')]);
        $this->mergeConfigFrom($source, 'events');
    }

    /**
     * Get the configuration path.
     *
     * @param  string $path
     * @return string
     */
    private function configPath($path = ''): string
    {
        return $this->app->make('path.config').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * @param string $producer
     * @param string $type
     * @param string $topic
     */
    private function registerGenericObservers(string $producer, string $type, string $topic): void
    {
        if (class_exists($producer)) {
            $this->registerCreatedObserver($producer, $type, $topic);
            $this->registerUpdatedObserver($producer, $type, $topic);
            $this->registerDeletedObserver($producer, $type, $topic);
        }
    }

    /**
     * @param string $producer
     * @param string $type
     * @param string $topic
     */
    private function registerCreatedObserver(string $producer, string $type, string $topic): void
    {
        if (method_exists($producer, 'created')) {
            $producer::created($this->app->make(CreatedObserver::class, [
                'producer' => function () {
                    return $this->app->make(ProducerService::class);
                },
                'message' => $this->makeMessage($type),
                'topic' => $topic,
            ]));
        }
    }

    /**
     * @param string $producer
     * @param string $type
     * @param string $topic
     */
    private function registerUpdatedObserver(string $producer, string $type, string $topic): void
    {
        if (method_exists($producer, 'updated')) {
            $producer::updated($this->app->make(UpdatedObserver::class, [
                'producer' => function () {
                    return $this->app->make(ProducerService::class);
                },
                'message' => $this->makeMessage($type),
                'topic' => $topic,
            ]));
        }
    }

    /**
     * @param string $producer
     * @param string $type
     * @param string $topic
     */
    private function registerDeletedObserver(string $producer, string $type, string $topic): void
    {
        if (method_exists($producer, 'deleted')) {
            $producer::deleted($this->app->make(DeletedObserver::class, [
                'producer' => function () {
                    return $this->app->make(ProducerService::class);
                },
                'message' => $this->makeMessage($type),
                'topic' => $topic,
            ]));
        }
    }

    /**
     * @return void
     */
    private function registerListeners(): void
    {
        $listeners = Config::get('events.listeners', []);

        foreach ($listeners as $type => $topic) {
            $this->getDispatcher()->listen("{$type}.*", $this->app->make(GenericObserver::class, [
                'producer' => function () {
                    return $this->app->make(ProducerService::class);
                },
                'message' => $this->makeMessage($type),
                'topic' => $topic,
            ]));
        }
    }

    /**
     * @param  string  $type
     * @return Message
     */
    private function makeMessage(string $type): Message
    {
        $source = Config::get('events.source', Source::UNDEFINED);
        $salt = Config::get('events.message.signature.salt', '');

        return $this->app
            ->make(Message::class)
            ->setType($type)
            ->setSource($source)
            ->setSalt($salt);
    }

    /**
     * @return Dispatcher
     */
    private function getDispatcher(): Dispatcher
    {
        return $this->app->make(Dispatcher::class);
    }
}
