<?php

namespace OneFit\Events;

use RdKafka\Conf;
use RdKafka\Producer;
use RdKafka\KafkaConsumer;
use OneFit\Events\Models\Message;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use OneFit\Events\Services\ConsumerService;
use OneFit\Events\Services\ProducerService;
use OneFit\Events\Observers\GenericObserver;
use Illuminate\Contracts\Foundation\Application;

/**
 * Class EventsServiceProvider.
 */
class EventsServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

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
        $this->registerObservers();
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
        $configuration->set('metadata.broker.list', env('METADATA_BROKER_LIST', 'localhost:9092'));

        // Set where to start consuming messages when there is no initial offset in
        // offset store or the desired offset is out of range.
        // 'smallest': start from the beginning
        $configuration->set('auto.offset.reset', env('AUTO_OFFSET_RESET', 'smallest'));

        // Default timeout for network requests
        $configuration->set('socket.timeout.ms', env('SOCKET_TIMEOUT_MS', 60000));

        // Produce exactly once and keep the original produce order
        $configuration->set('enable.idempotence', env('ENABLE_IDEMPOTENCE', 'false'));

        // Fetch only the topics in use, reduce the bandwidth
        $configuration->set('topic.metadata.refresh.sparse', env('TOPIC_METADATA_REFRESH_SPARSE', 'true'));
        $configuration->set('topic.metadata.refresh.interval.ms', env('TOPIC_METADATA_REFRESH_INTERVAL_MS', 300000));

        // Signal that librdkafka will use to quickly terminate on rd_kafka_destroy()
        pcntl_sigprocmask(SIG_BLOCK, [env('INTERNAL_TERMINATION_SIGNAL', 29)]);
        $configuration->set('internal.termination.signal', env('INTERNAL_TERMINATION_SIGNAL', 29));
    }

    /**
     * @return void
     */
    private function registerProducer(): void
    {
        $this->app->singleton(ProducerService::class, function (Application $app) {
            $configuration = $app->make(Conf::class);
            $this->setConfiguration($configuration);

            $producer = $app->make(Producer::class, ['conf' => $configuration]);

            return new ProducerService($producer);
        });
    }

    /**
     * @return void
     */
    private function registerConsumer(): void
    {
        $this->app->bind(ConsumerService::class, function (Application $app, array $params = []) {
            $configuration = $app->make(Conf::class);
            $this->setConfiguration($configuration);

            if (isset($params['group_id'])) {
                $configuration->set('group.id', $params['group_id']);
            }

            $consumer = $app->make(KafkaConsumer::class, ['conf' => $configuration]);

            return new ConsumerService($consumer);
        });
    }

    /**
     * @return void
     */
    private function registerObservers(): void
    {
        $producers = Config::get('event.producers', []);

        foreach ($producers as $domain => $domainProducers) {
            if (is_array($domainProducers)) {
                array_walk($producers, function ($type, $producer, $domain) {
                    $this->registerObserver($type, $producer, $domain);
                });
            }
        }
    }

    /**
     * Setup exact configuration.
     */
    protected function setupConfig()
    {
        $source = realpath(__DIR__.'../config/event.php');
        $this->publishes([$source => $this->configPath('event.php')]);
        $this->mergeConfigFrom($source, 'event');
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
     * @param string $type
     * @param string $producer
     * @param string $domain
     */
    private function registerObserver(string $type, string $producer, string $domain): void
    {
        if (class_exists($producer) && method_exists($producer, 'observe')) {
            $message = $this->app->make(Message::class, ['type' => $type]);
            $producer::observe($this->app->make(GenericObserver::class, [
                'producer' => $this->app->make(ProducerService::class),
                'message' => $message,
                'domain' => $domain,
            ]));
        }
    }
}
