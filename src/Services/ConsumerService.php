<?php

namespace OneFit\Events\Services;

use Closure;
use AvroSchema;
use Monolog\Logger;
use RdKafka\KafkaConsumer;
use Illuminate\Support\Arr;
use AvroSchemaParseException;
use OneFit\Events\Models\Message;
use RdKafka\Exception as RdKafkaException;
use FlixTech\AvroSerializer\Objects\RecordSerializer;
use FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException;

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
     * @var Closure
     */
    private $serializer;

    /**
     * @var array
     */
    private $schemas;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * ConsumerService constructor.
     *
     * @param KafkaConsumer $consumer
     * @param Message       $message
     * @param Closure       $serializer
     * @param array         $schemas
     * @param Logger|null   $logger
     */
    public function __construct(KafkaConsumer $consumer, Message $message, Closure $serializer, array $schemas, ?Logger $logger = null)
    {
        $this->consumer = $consumer;
        $this->message = $message;
        $this->serializer = $serializer;
        $this->schemas = $schemas;
        $this->logger = $logger;
    }

    /**
     * @param array $topics
     *
     * @throws RdKafkaException
     * @return ConsumerService
     */
    public function subscribe(array $topics): self
    {
        $this->consumer->subscribe($topics);

        return $this;
    }

    /**
     * @param int $timeout
     *
     * @throws RdKafkaException
     * @throws SchemaRegistryException
     * @return Message
     */
    public function consume(int $timeout): Message
    {
        $message = $this->getMessage();

        try {
            $kafkaMessage = $this->consumer->consume($timeout);
            switch ($kafkaMessage->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $message->hydrate($this->decodeMessage($kafkaMessage->payload, $kafkaMessage->topic_name));
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    $message->setError($kafkaMessage->errstr());
                    break;
                default:
                    $message->setError($kafkaMessage->errstr());
                    $this->logger->error($kafkaMessage->errstr(), [
                        'message' => $kafkaMessage,
                    ]);
                    break;
            }
        } catch (\Exception $ex) {
            $message->setError($ex->getMessage());
            $this->logger->error($ex->getMessage(), [
                'exception' => $ex,
                'metadata'  => $this->consumer->getMetadata(false, null, $timeout),
                'topics'    => $this->consumer->getSubscription(),
            ]);
        }

        return $message;
    }

    /**
     * @return Message
     */
    private function getMessage(): Message
    {
        return clone $this->message;
    }

    /**
     * @param string $message
     * @param string $topic
     *
     * @throws SchemaRegistryException
     * @throws AvroSchemaParseException
     * @return array
     */
    private function decodeMessage(string $message, string $topic): array
    {
        if (isset($this->schemas['path'][$topic])) {
            return $this->decodeForSchema($message, $topic);
        }

        return json_decode($message, true);
    }

    /**
     * @param string $message
     * @param string $topic
     *
     * @throws SchemaRegistryException
     * @throws AvroSchemaParseException
     * @return array
     */
    private function decodeForSchema(string $message, string $topic): array
    {
        $mapped = [];
        $path = $this->schemas['path'][$topic] ?? '';
        $mapping = $this->schemas['mapping'][$topic] ?? [];
        $items = $this->getSerializer()->decodeMessage($message, AvroSchema::parse(file_get_contents($path)));

        foreach ($mapping as $from => $to) {
            Arr::set($mapped, $from, Arr::get($items, $to));
        }

        return $mapped;
    }

    /**
     * @return RecordSerializer
     */
    private function getSerializer(): RecordSerializer
    {
        return call_user_func($this->serializer);
    }

    /**
     * @throws RdKafkaException
     */
    public function commit(): void
    {
        try {
            $this->consumer->commit();
        } catch (\Exception $ex) {
            $this->logger->error($ex->getMessage(), [
                'exception' => $ex,
                'metadata'  => $this->consumer->getMetadata(false, null, 60e3),
                'topics'    => $this->consumer->getSubscription(),
            ]);
        }
    }

    /**
     * @throws RdKafkaException
     */
    public function commitAsync(): void
    {
        try {
            $this->consumer->commitAsync();
        } catch (\Exception $ex) {
            $this->logger->error($ex->getMessage(), [
                'exception' => $ex,
                'metadata'  => $this->consumer->getMetadata(false, null, 60e3),
                'topics'    => $this->consumer->getSubscription(),
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
}
