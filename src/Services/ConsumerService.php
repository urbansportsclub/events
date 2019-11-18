<?php

namespace OneFit\Events\Services;

use RdKafka\KafkaConsumer;
use RdKafka\Message;

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
     */
    public function subscribe(array $topics): void
    {
        $this->consumer->subscribe($topics);
    }

    /**
     * @param int $timeout
     * @return Message
     * @throws \RdKafka\Exception
     */
    public function consume(int $timeout): Message
    {
        return $this->consumer->consume($timeout);
    }
}
