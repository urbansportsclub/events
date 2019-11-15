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
     * Register wildcard events listener.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        if (env('ATTACH_GLOBAL_LISTENER', false)) {
            Event::listen('*', function ($event, array $payload) {
                $message = $this->app->make(Message::class, ['event' => $event, ['payload' => $payload]]);
                $producer = $this->app->make(ProducerService::class);
                $producer->produce(Topic::EVENT_GLOBAL, $message);
            });
        }
    }

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
