<?php

namespace OneFit\Events\Services;

use RdKafka\Producer;
use OneFit\Events\Models\Message;

/**
 * Class ProducerService.
 */
class ProducerService
{
    /**
     * @var Producer
     */
    private $producer;

    /**
     * @var int
     */
    private $timeout;

    /**
     * @var int
     */
    private $retries;

    /**
     * ProducerService constructor.
     * @param Producer $producer
     * @param int      $timeout
     * @param int      $retries
     */
    public function __construct(Producer $producer, int $timeout, int $retries)
    {
        $this->producer = $producer;
        $this->timeout = $timeout;
        $this->retries = $retries;
    }

    /**
     * @param Message $message
     * @param string  $topic
     */
    public function produce(Message $message, string $topic): void
    {
        $topic = $this->producer->newTopic($topic);
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, json_encode($message, JSON_FORCE_OBJECT), $message->getSignature());
        $this->producer->poll(0);
    }

    /**
     * @param int $counter
     */
    public function flush(int $counter = 0): void
    {
        $response = $this->producer->flush($this->timeout);

        if (++$counter >= $this->retries) {
            throw new \RuntimeException('Was unable to flush, messages might be lost!', $response);
        }

        if (RD_KAFKA_RESP_ERR_NO_ERROR !== $response) {
            $this->flush($counter);
        }
    }
}
