<?php

namespace OneFit\Events\Services;

use Closure;
use AvroSchema;
use RdKafka\Producer;
use Illuminate\Support\Arr;
use OneFit\Events\Models\Message;
use FlixTech\AvroSerializer\Objects\RecordSerializer;

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
     * @var Closure
     */
    private $serializer;

    /**
     * @var array
     */
    private $schemas;

    /**
     * ProducerService constructor.
     * @param Producer $producer
     * @param array    $schemas
     * @param Closure  $serializer
     * @param int      $timeout
     * @param int      $retries
     */
    public function __construct(Producer $producer, Closure $serializer, array $schemas, int $timeout, int $retries)
    {
        $this->producer = $producer;
        $this->serializer = $serializer;
        $this->schemas = $schemas;
        $this->timeout = $timeout;
        $this->retries = $retries;
    }

    /**
     * @param  Message                                                       $message
     * @param  string                                                        $topic
     * @throws \AvroSchemaParseException
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     */
    public function produce(Message $message, string $topic): void
    {
        $producerTopic = $this->producer->newTopic($topic);
        $producerTopic->produce(RD_KAFKA_PARTITION_UA, 0, $this->encodeMessage($message, $topic), $message->getSignature());
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

    /**
     * @param  Message                                                       $message
     * @param  string                                                        $topic
     * @throws \AvroSchemaParseException
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     * @return string
     */
    private function encodeMessage(Message $message, string $topic): string
    {
        if (isset($this->schemas['path'][$topic], $this->schemas['mapping'][$topic])) {
            return $this->encodeForSchema($message, $topic);
        }

        return json_encode($message, JSON_FORCE_OBJECT);
    }

    /**
     * @return RecordSerializer
     */
    private function getSerializer(): RecordSerializer
    {
        return call_user_func($this->serializer);
    }

    /**
     * @param  Message                                                       $message
     * @param  string                                                        $topic
     * @throws \AvroSchemaParseException
     * @throws \FlixTech\SchemaRegistryApi\Exception\SchemaRegistryException
     * @return string
     */
    private function encodeForSchema(Message $message, string $topic): string
    {
        $mapped = [];
        $data = $message->jsonSerialize();
        $path = $this->schemas['path'][$topic] ?? '';
        $mapping = $this->schemas['mapping'][$topic] ?? [];
        $conversion = $this->schemas['conversion'][$topic] ?? [];

        foreach ($mapping as $from => $to) {
            $converted = is_callable($conversion[$from]) ?
                call_user_func($conversion[$from], Arr::get($data, $from)) :
                Arr::get($data, $from);

            Arr::set($mapped, $to, $converted);
        }

        return $this->getSerializer()->encodeRecord($message->getType(), AvroSchema::parse(file_get_contents($path)), $mapped);
    }
}
