<?php

namespace OneFit\Events\Services;

use RdKafka\Message;
use RdKafka\KafkaConsumer;

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
        return $this->consumer->consume($timeout);
    }

    /**
     * @param Message|null $message
     * @throws \RdKafka\Exception
     */
    public function commit(Message $message = null): void
    {
        $this->consumer->commitAsync($message);
    }
}
