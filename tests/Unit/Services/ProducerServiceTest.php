<?php

namespace OneFit\Events\Tests\Unit\Services;

use AvroSchema;
use RdKafka\Producer;
use RdKafka\ProducerTopic;
use PHPUnit\Framework\TestCase;
use OneFit\Events\Models\Message;
use OneFit\Events\Services\ProducerService;
use PHPUnit\Framework\MockObject\MockClass;
use FlixTech\AvroSerializer\Objects\RecordSerializer;

/**
 * Class ProducerServiceTest.
 */
class ProducerServiceTest extends TestCase
{
    /**
     * @var Producer|MockClass
     */
    private $producerMock;

    /**
     * @var ProducerTopic|MockClass
     */
    private $topicMock;

    /**
     * @var Message|MockClass
     */
    private $messageMock;

    /**
     * @var RecordSerializer|MockClass
     */
    private $serializerMock;

    /**
     * @var ProducerService
     */
    private $producerService;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->producerMock = $this->createMock(Producer::class);
        $this->topicMock = $this->createMock(ProducerTopic::class);
        $this->messageMock = $this->createMock(Message::class);
        $this->serializerMock = $this->createMock(RecordSerializer::class);
        $schemas = [
            'path' => [
                'my-avro-topic' => __DIR__ . '/../stubs/avro-event-schema.json',
            ],
            'mapping' => [
                'my-avro-topic' => [
                    'event' => 'event.action',
                ],
            ],
            'conversion' => [
                'my-avro-topic' => [
                    'event' => 'strtoupper',
                ],
            ]
        ];

        $serializer = function () {
            return $this->serializerMock;
        };

        $this->producerService = new ProducerService($this->producerMock, $serializer, $schemas, 3000, 3);

        parent::setUp();
    }

    /** @test */
    public function can_call_produce()
    {
        $this->producerMock
            ->expects($this->once())
            ->method('newTopic')
            ->willReturn($this->topicMock);

        $this->topicMock
            ->expects($this->once())
            ->method('produce')
            ->with(RD_KAFKA_PARTITION_UA, 0, json_encode($this->messageMock));

        $this->producerMock
            ->expects($this->once())
            ->method('poll')
            ->with(0);

        $this->producerService->produce($this->messageMock, 'member_domain');
    }

    /** @test */
    public function can_call_flush()
    {
        $this->producerMock
            ->expects($this->exactly(2))
            ->method('flush')
            ->with(3000)
            ->willReturnOnConsecutiveCalls(
                RD_KAFKA_RESP_ERR_UNKNOWN,
                RD_KAFKA_RESP_ERR_NO_ERROR
            );

        $this->producerService->flush();
    }

    /** @test */
    public function unsuccessful_flush_will_throw_runtime_exception()
    {
        $this->producerMock
            ->expects($this->exactly(3))
            ->method('flush')
            ->with(3000)
            ->willReturn(RD_KAFKA_RESP_ERR_UNKNOWN);

        $this->expectException(\RuntimeException::class);

        $this->producerService->flush();
    }

    /** @test */
    public function can_produce_avro_encoded_message()
    {
        $encodedMessage = file_get_contents(__DIR__ . '/../stubs/avro-event-encoded');

        $this->producerMock
            ->expects($this->once())
            ->method('newTopic')
            ->willReturn($this->topicMock);

        $this->messageMock
            ->expects($this->once())
            ->method('jsonSerialize')
            ->willReturn(['event' => 'event-triggered']);

        $this->serializerMock
            ->expects($this->once())
            ->method('encodeRecord')
            ->with(
                'my-avro-topic',
                AvroSchema::parse(file_get_contents(__DIR__ . '/../stubs/avro-event-schema.json')),
                ['event' => ['action' => 'EVENT-TRIGGERED']]
            )->willReturn(file_get_contents(__DIR__ . '/../stubs/avro-event-encoded'));

        $this->topicMock
            ->expects($this->once())
            ->method('produce')
            ->with(RD_KAFKA_PARTITION_UA, 0, $encodedMessage);

        $this->producerMock
            ->expects($this->once())
            ->method('poll')
            ->with(0);

        $this->producerService->produce($this->messageMock, 'my-avro-topic');
    }
}
