<?php

namespace OneFit\Events\Services;

use RdKafka\KafkaConsumer;
use OneFit\Events\Models\Message;
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
     * @var Message
     */
    private $message;

    /**
     * ConsumerService constructor.
     * @param KafkaConsumer $consumer
     * @param Message       $message
     */
    public function __construct(KafkaConsumer $consumer, Message $message)
    {
        $this->consumer = $consumer;
        $this->message = $message;
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
        $message = $this->getMessage();

        try {
            $kafkaMessage = $this->consumer->consume($timeout);
            switch ($kafkaMessage->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $payload = json_decode($kafkaMessage->payload, true);
                    is_array($payload) && $message->hydrate($payload);
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    $message->setError($kafkaMessage->errstr());
                    break;
                default:
                    $message->setError($kafkaMessage->errstr());
                    Log::error($kafkaMessage->errstr(), [
                        'message' => $kafkaMessage,
                    ]);
                    break;
            }
        } catch (\Exception $ex) {
            $message->setError($ex->getMessage());
            Log::error($ex->getMessage(), [
                'exception' => $ex,
                'metadata' => $this->consumer->getMetadata(false, null, $timeout),
                'topics' => $this->consumer->getSubscription(),
            ]);
        }

        return $message;
    }

    /**
     * @throws \RdKafka\Exception
     */
    public function commit(): void
    {
        try {
            $this->consumer->commitAsync();
        } catch (\Exception $ex) {
            Log::error($ex->getMessage(), [
                'exception' => $ex,
                'metadata' => $this->consumer->getMetadata(false, null, 60e3),
                'topics' => $this->consumer->getSubscription(),
            ]);
        }
    }

    /**
     * @return void
     */
    public function close(): void
    {
        $this->consumer->close();
    }

    /**
     * @return Message
     */
    private function getMessage(): Message
    {
        return clone $this->message;
    }
}
