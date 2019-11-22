<?php

namespace OneFit\Events\Services;

use RdKafka\Message;
use RdKafka\KafkaConsumer;
use Illuminate\Support\Facades\Log;

/**
 * Class ConsumerService.
 */
class ConsumerService
{
    /**
     * @var KafkaConsumer
     */
    private $consumer;

    /**
     * ConsumerService constructor.
     * @param KafkaConsumer $consumer
     */
    public function __construct(KafkaConsumer $consumer)
    {
        $this->consumer = $consumer;
    }

    /**
     * @param  array              $topics
     * @throws \RdKafka\Exception
     * @return ConsumerService
     */
    public function subscribe(array $topics): self
    {
        $this->consumer->subscribe($topics);

        return $this;
    }

    /**
     * @param  int                $timeout
     * @throws \RdKafka\Exception
     * @return Message
     */
    public function consume(int $timeout): Message
    {
        try {
            return $this->consumer->consume($timeout);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage(), [
                'exception' => $ex,
                'metadata' => $this->consumer->getMetadata(false, null, $timeout),
                'topics' => $this->consumer->getSubscription(),
            ]);
        }
    }

    /**
     * @param  Message|null       $message
     * @throws \RdKafka\Exception
     */
    public function commit(Message $message = null): void
    {
        try {
            $this->consumer->commitAsync($message);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage(), [
                'exception' => $ex,
                'message' => $message,
                'metadata' => $this->consumer->getMetadata(false, null, 60e3),
                'topics' => $this->consumer->getSubscription(),
            ]);
        }
    }
}
