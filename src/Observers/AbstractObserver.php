<?php

namespace OneFit\Events\Observers;

use Closure;
use OneFit\Events\Models\Event;
use OneFit\Events\Models\Message;
use Illuminate\Support\Facades\Log;

/**
 * Class AbstractObserver.
 */
abstract class AbstractObserver
{
    /**
     * @var Closure
     */
    private $producer;

    /**
     * @var Message
     */
    private $message;

    /**
     * @var string
     */
    private $topic;

    /**
     * AbstractObserver constructor.
     * @param Closure $producer
     * @param Message $message
     * @param string  $topic
     */
    public function __construct(Closure $producer, Message $message, string $topic)
    {
        $this->producer = $producer;
        $this->message = $message;
        $this->topic = $topic;
    }

    /**
     * @param array $payload
     */
    protected function created(array $payload): void
    {
        $message = $this->createMessage($payload, Event::EVENT_CREATED);
        $this->produce($message);
    }

    /**
     * @param array $payload
     */
    protected function updated(array $payload): void
    {
        $message = $this->createMessage($payload, Event::EVENT_UPDATED);
        $this->produce($message);
    }

    /**
     * @param array $payload
     */
    protected function deleted(array $payload): void
    {
        $message = $this->createMessage($payload, Event::EVENT_DELETED);
        $this->produce($message);
    }

    /**
     * @param array  $payload
     * @param string $event
     */
    protected function custom(array $payload, string $event): void
    {
        $message = $this->createMessage($payload, $event);
        $this->produce($message);
    }

    /**
     * @param  array   $payload
     * @param  string  $event
     * @return Message
     */
    private function createMessage(array $payload, string $event): Message
    {
        return $this->getMessage()
            ->setEvent($event)
            ->setPayload($payload);
    }

    /**
     * @param Message $message
     */
    private function produce(Message $message): void
    {
        try {
            $producer = call_user_func($this->producer);
            $producer->produce($message, $this->topic);
            $producer->flush();
        } catch (\Exception $ex) {
            Log::error($ex->getMessage(), [
                'code' => $ex->getCode(),
                'message' => json_encode($message, JSON_FORCE_OBJECT),
                'topic' => $this->topic,
            ]);
        }
    }

    /**
     * @return Message
     */
    private function getMessage(): Message
    {
        return clone $this->message;
    }
}
