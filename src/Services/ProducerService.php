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
    private $retries;

    /**
     * ProducerService constructor.
     * @param Producer $producer
     * @param int      $retries
     */
    public function __construct(Producer $producer, int $retries)
    {
        $this->producer = $producer;
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
        $this->flushProducer();
    }

    /**
     * @param int $counter
     */
    private function flushProducer(int $counter = 0): void
    {
        $response = $this->producer->flush(1000);

        if ($counter >= $this->retries) {
            throw new \RuntimeException('Was unable to flush, messages might be lost!', $response);
        }

        if (RD_KAFKA_RESP_ERR_NO_ERROR !== $response) {
            $this->flushProducer(++$counter);
        }
    }
}
