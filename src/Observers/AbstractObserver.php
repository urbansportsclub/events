<?php

namespace OneFit\Events\Observers;

use Closure;
use OneFit\Events\Models\Event;
use OneFit\Events\Models\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\QueueableEntity;

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
     * @param QueueableEntity $entity
     */
    protected function created(QueueableEntity $entity): void
    {
        $message = $this->createMessage($entity, Event::EVENT_CREATED);
        $this->produce($message);
    }

    /**
     * @param QueueableEntity $entity
     */
    protected function updated(QueueableEntity $entity): void
    {
        $message = $this->createMessage($entity, Event::EVENT_UPDATED);
        $this->produce($message);
    }

    /**
     * @param QueueableEntity $entity
     */
    protected function deleted(QueueableEntity $entity): void
    {
        $message = $this->createMessage($entity, Event::EVENT_DELETED);
        $this->produce($message);
    }

    /**
     * @param string          $event
     * @param QueueableEntity $entity
     */
    protected function custom(string $event, QueueableEntity $entity): void
    {
        $split = explode('.', $event);
        $message = $this->createMessage($entity, end($split));
        $this->produce($message);
    }

    /**
     * @param  QueueableEntity $entity
     * @param  string          $event
     * @return Message
     */
    private function createMessage(QueueableEntity $entity, string $event): Message
    {
        return $this->getMessage()
            ->setEvent($event)
            ->setId(strval($entity->getQueueableId()))
            ->setConnection(strval($entity->getQueueableConnection()))
            ->setPayload(json_encode($entity, JSON_FORCE_OBJECT));
    }

    /**
     * @param Message $message
     */
    private function produce(Message $message): void
    {
        $producer = $this->producer;
        try {
            $producer()->produce($message, $this->topic);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage(), [
                'exception' => $ex,
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
