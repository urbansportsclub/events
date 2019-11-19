<?php

namespace OneFit\Events\Observers;

use OneFit\Events\Models\Message;
use Illuminate\Support\Facades\Log;
use OneFit\Events\Services\ProducerService;
use Illuminate\Contracts\Queue\QueueableEntity;

/**
 * Class AbstractObserver
 * @package OneFit\Events\Observers
 */
class AbstractObserver
{
    private const EVENT_CREATED = 'created';
    private const EVENT_UPDATED = 'updated';
    private const EVENT_DELETED = 'deleted';

    /**
     * @var ProducerService
     */
    private $producer;

    /**
     * @var Message
     */
    private $message;

    /**
     * @var string
     */
    private $domain;

    /**
     * AbstractObserver constructor.
     * @param ProducerService $producer
     * @param Message         $message
     * @param string          $domain
     */
    public function __construct(ProducerService $producer, Message $message, string $domain)
    {
        $this->producer = $producer;
        $this->message = $message;
        $this->domain = $domain;
    }

    /**
     * @param QueueableEntity $entity
     */
    protected function created(QueueableEntity $entity): void
    {
        $message = $this->createMessage($entity, self::EVENT_CREATED);
        $this->produce($message);
    }

    /**
     * @param QueueableEntity $entity
     */
    protected function updated(QueueableEntity $entity): void
    {
        $message = $this->createMessage($entity, self::EVENT_UPDATED);
        $this->produce($message);
    }

    /**
     * @param QueueableEntity $entity
     */
    protected function deleted(QueueableEntity $entity): void
    {
        $message = $this->createMessage($entity, self::EVENT_DELETED);
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
        try {
            $this->producer->produce($message, $this->domain);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage(), [
                'exception' => $ex,
                'message' => json_encode($message, JSON_FORCE_OBJECT),
                'topic' => $this->domain,
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
