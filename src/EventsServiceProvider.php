<?php

namespace OneFit\Events;

use RdKafka\Conf;
use RdKafka\Producer;
use RdKafka\KafkaConsumer;
use OneFit\Events\Models\Topic;
use OneFit\Events\Models\Message;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use OneFit\Events\Services\ConsumerService;
use OneFit\Events\Services\ProducerService;

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
        $this->app->singleton(ProducerService::class, function () {
            $configuration = $this->buildConfiguration();
            $producer = new Producer($configuration);

            return new ProducerService($producer);
        });

        $this->app->bind(ConsumerService::class, function ($app, array $params = []) {
            $configuration = $this->buildConfiguration();
            if (isset($params['group_id'])) {
                $configuration->set('group.id', $params['group_id']);
            }
            $consumer = new KafkaConsumer($configuration);

            return new ConsumerService($consumer);
        });
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
     * @return Conf
     */
    private function buildConfiguration(): Conf
    {
        $configuration = new Conf();

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

        return $configuration;
    }
}
