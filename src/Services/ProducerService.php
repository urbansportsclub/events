<?php

namespace OneFit\Events\Services;

use RdKafka\Producer;
use OneFit\Events\Models\Message;
use Illuminate\Support\Facades\Config;

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
     * ProducerService constructor.
     * @param Producer $producer
     */
    public function __construct(Producer $producer)
    {
        $this->producer = $producer;
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
        $this->flushProducer(Config::get('events.flush.retries'));
    }

    /**
     * @param int $retries
     * @param int $counter
     */
    private function flushProducer(int $retries, int $counter = 0): void
    {
        $response = $this->producer->flush(Config::get('events.flush.timeout.ms'));

        if ($counter >= $retries) {
            throw new \RuntimeException('Was unable to flush, messages might be lost!', $response);
        }

        if (RD_KAFKA_RESP_ERR_NO_ERROR !== $response) {
            $this->flushProducer($retries, ++$counter);
        }
    }
}
