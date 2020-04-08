<?php

namespace OneFit\Events\Services;

use AvroSchemaParseException;
use Closure;
use AvroSchema;
use FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException;
use RdKafka\Exception as RdKafkaException;
use RdKafka\KafkaConsumer;
use Illuminate\Support\Arr;
use OneFit\Events\Models\Message;
use Illuminate\Support\Facades\Log;
use FlixTech\AvroSerializer\Objects\RecordSerializer;

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
     * @var array
     */
    private $schemas;

    /**
     * @var Closure
     */
    private $serializer;

    /**
     * ConsumerService constructor.
     * @param KafkaConsumer $consumer
     * @param Message       $message
     * @param Closure       $serializer
     * @param array         $schemas
     */
    public function __construct(KafkaConsumer $consumer, Message $message, Closure $serializer, array $schemas)
    {
        $this->consumer = $consumer;
        $this->message = $message;
        $this->serializer = $serializer;
        $this->schemas = $schemas;
    }

    /**
     * @param  array              $topics
     * @return ConsumerService
     *@throws RdKafkaException
     */
    public function subscribe(array $topics): self
    {
        $this->consumer->subscribe($topics);

        return $this;
    }

    /**
     * @param int $timeout
     * @return Message
     * @throws SchemaRegistryException
     * @throws RdKafkaException
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
     * @throws RdKafkaException
     */
    public function commit(): void
    {
        try {
            $this->consumer->commit();
        } catch (\Exception $ex) {
            Log::error($ex->getMessage(), [
                'exception' => $ex,
                'metadata' => $this->consumer->getMetadata(false, null, 60e3),
                'topics' => $this->consumer->getSubscription(),
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

    /**
     * @return RecordSerializer
     */
    private function getSerializer(): RecordSerializer
    {
        return call_user_func($this->serializer);
    }

    /**
     * @param string $message
     * @param string $topic
     * @return array
     * @throws AvroSchemaParseException
     * @throws SchemaRegistryException
     */
    private function decodeMessage(string $message, string $topic): array
    {
        if (isset($this->schemas['path'][$topic], $this->schemas['mapping'][$topic])) {
            return $this->decodeForSchema($message, $this->schemas['path'][$topic], $this->schemas['mapping'][$topic]);
        }

        return json_decode($message, true);
    }

    /**
     * @param string $message
     * @param string $path
     * @param array $mapping
     * @return array
     * @throws AvroSchemaParseException
     * @throws SchemaRegistryException
     */
    private function decodeForSchema(string $message, string $path, array $mapping): array
    {
        $mapped = [];
        $items = $this->getSerializer()->decodeMessage($message, AvroSchema::parse(file_get_contents($path)));

        foreach ($mapping as $from => $to) {
            Arr::set($mapped, $from, Arr::get($items, $to));
        }

        return $mapped;
    }
}
