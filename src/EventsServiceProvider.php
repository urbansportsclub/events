<?php

namespace OneFit\Events;

use Illuminate\Support\ServiceProvider;
use OneFit\Events\Services\ConsumerService;
use OneFit\Events\Services\ProducerService;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;
use RdKafka\Producer;

/**
 * Class EventsServiceProvider
 * @package OneFit\Events
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
        $this->app->singleton(ProducerService::class, function ($app) {
            $configuration = new Conf();
            $producer = new Producer($configuration);

            return new ProducerService($producer, $configuration);
        });

        $this->app->singleton(ConsumerService::class, function ($app) {
            $configuration = new Conf();
            $consumer = new KafkaConsumer($configuration);

            return new ConsumerService($consumer, $configuration);
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
}
