<?php

namespace OneFit\Events\Tests\Unit\Services;

use AvroSchema;
use RdKafka\Metadata;
use RdKafka\KafkaConsumer;
use PHPUnit\Framework\TestCase;
use OneFit\Events\Models\Message;
use Illuminate\Support\Facades\Log;
use RdKafka\Message as KafkaMessage;
use OneFit\Events\Services\ConsumerService;
use PHPUnit\Framework\MockObject\MockClass;
use FlixTech\AvroSerializer\Objects\RecordSerializer;

/**
 * Class ConsumerServiceTest.
 */
class ConsumerServiceTest extends TestCase
{
    /**
     * @var KafkaConsumer|MockClass
     */
    private $consumerMock;

    /**
     * @var KafkaMessage|MockClass
     */
    private $kafkaMessageMock;

    /**
     * @var Message|MockClass
     */
    private $messageMock;

    /**
     * @var RecordSerializer|MockClass
     */
    private $serializerMock;

    /**
     * @var ConsumerService
     */
    private $consumerService;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->consumerMock = $this->createMock(KafkaConsumer::class);
        $this->kafkaMessageMock = $this->createMock(KafkaMessage::class);
        $this->messageMock = $this->createMock(Message::class);
        $this->serializerMock = $this->createMock(RecordSerializer::class);
        $schemas = [
            'path' => [
                'my-avro-topic' => __DIR__ . '/../stubs/avro-event-schema.json',
            ],
            'mapping' => [
                'my-avro-topic' => [
                    'payload.uuid' => 'id',
                ],
            ],
        ];

        $serializer = function () {
            return $this->serializerMock;
        };

        $this->consumerService = new ConsumerService($this->consumerMock, $this->messageMock, $serializer, $schemas);
    }

    /** @test */
    public function can_subscribe_to_topics()
    {
        $topics = ['friend_request_received', 'friend_request_sent'];

        $this->consumerMock
            ->expects($this->once())
            ->method('subscribe')
            ->with($topics);

        $this->consumerService->subscribe($topics);
    }

    /** @test */
    public function can_consume_stored_messages()
    {
        $this->kafkaMessageMock->topic_name = 'my-topic';
        $this->kafkaMessageMock->payload = '{"key": "value"}';

        $this->consumerMock
            ->expects($this->once())
            ->method('consume')
            ->with(120000)
            ->willReturn($this->kafkaMessageMock);

        $response = $this->consumerService->consume(120000);

        $this->assertEquals($this->messageMock, $response);
    }

    /** @test */
    public function can_commit_offset()
    {
        $this->consumerMock
            ->expects($this->once())
            ->method('commit');

        $this->consumerService->commit();
    }

    /** @test */
    public function failing_to_commit_will_fail_gracefully()
    {
        Log::spy();
        $topics = ['test_topic'];
        $metadata = $this->createMock(Metadata::class);
        $message = 'failed to commit';
        $exception = new \RdKafka\Exception($message);

        $this->consumerMock
            ->expects($this->once())
            ->method('commit')
            ->willThrowException($exception);

        $this->consumerMock
            ->expects($this->once())
            ->method('getMetadata')
            ->with(false, null, 60e3)
            ->willReturn($metadata);

        $this->consumerMock
            ->expects($this->once())
            ->method('getSubscription')
            ->willReturn($topics);

        Log::shouldReceive('error')
            ->with($message, [
                'exception' => $exception,
                'metadata' => $metadata,
                'topics' => $topics,
            ]);

        $this->consumerService->commit();
    }

    /** @test */
    public function can_commit_async_offset()
    {
        $this->consumerMock
            ->expects($this->once())
            ->method('commitAsync');

        $this->consumerService->commitAsync();
    }

    /** @test */
    public function failing_to_commit_async_will_fail_gracefully()
    {
        Log::spy();
        $topics = ['test_topic'];
        $metadata = $this->createMock(Metadata::class);
        $message = 'failed to commit';
        $exception = new \RdKafka\Exception($message);

        $this->consumerMock
            ->expects($this->once())
            ->method('commitAsync')
            ->willThrowException($exception);

        $this->consumerMock
            ->expects($this->once())
            ->method('getMetadata')
            ->with(false, null, 60e3)
            ->willReturn($metadata);

        $this->consumerMock
            ->expects($this->once())
            ->method('getMetadata')
            ->with(false, null, 60e3)
            ->willReturn($topics);

        Log::shouldReceive('error')
            ->with($message, [
                'exception' => $exception,
                'metadata' => $exception,
                'topics' => $topics,
            ]);

        $this->consumerService->commitAsync();
    }

    /** @test */
    public function can_close_consumer_connection()
    {
        $this->consumerMock
            ->expects($this->once())
            ->method('close');

        $this->consumerService->close();
    }

    /** @test */
    public function can_consume_avro_encoded_message()
    {
        $this->kafkaMessageMock->topic_name = 'my-avro-topic';
        $this->kafkaMessageMock->payload = file_get_contents(__DIR__ . '/../stubs/avro-event-encoded');

        $this->consumerMock
            ->expects($this->once())
            ->method('consume')
            ->with(120000)
            ->willReturn($this->kafkaMessageMock);

        $this->serializerMock
            ->expects($this->once())
            ->method('decodeMessage')
            ->with($this->kafkaMessageMock->payload, AvroSchema::parse(file_get_contents(__DIR__ . '/../stubs/avro-event-schema.json')))
            ->willReturn(['id' => 'event-uuid']);

        $this->messageMock
            ->expects($this->once())
            ->method('hydrate')
            ->with(['payload' => ['uuid' => 'event-uuid']])
            ->willReturn($this->messageMock);

        $response = $this->consumerService->consume(120000);

        $this->assertEquals($this->messageMock, $response);
    }

    /** @test */
    public function will_fail_gracefully_when_request_times_out()
    {
        $this->kafkaMessageMock->err = RD_KAFKA_RESP_ERR__TIMED_OUT;

        $this->consumerMock
            ->expects($this->once())
            ->method('consume')
            ->with(120000)
            ->willReturn($this->kafkaMessageMock);

        $this->kafkaMessageMock
            ->expects($this->once())
            ->method('errstr')
            ->willReturn('request timed out');

        $this->messageMock
            ->expects($this->once())
            ->method('setError')
            ->with('request timed out')
            ->willReturn($this->messageMock);

        $response = $this->consumerService->consume(120000);

        $this->assertEquals($this->messageMock, $response);
    }

    /** @test */
    public function will_fail_gracefully_for_unhandled_error_code()
    {
        Log::spy();
        $this->kafkaMessageMock->err = RD_KAFKA_RESP_ERR__ALL_BROKERS_DOWN;

        $this->consumerMock
            ->expects($this->once())
            ->method('consume')
            ->with(120000)
            ->willReturn($this->kafkaMessageMock);

        $this->kafkaMessageMock
            ->expects($this->exactly(2))
            ->method('errstr')
            ->willReturn('all brokers are down');

        $this->messageMock
            ->expects($this->once())
            ->method('setError')
            ->with('all brokers are down')
            ->willReturn($this->messageMock);

        Log::shouldReceive('error')
            ->with('all brokers are down', [
                'message' => $this->kafkaMessageMock,
            ]);

        $response = $this->consumerService->consume(120000);

        $this->assertEquals($this->messageMock, $response);
    }

    /** @test */
    public function will_fail_gracefully_if_exception_is_raised()
    {
        Log::spy();
        $topics = ['test_topic'];
        $metadata = $this->createMock(Metadata::class);
        $message = 'exception was raised';
        $exception = new \RdKafka\Exception($message);

        $this->consumerMock
            ->expects($this->once())
            ->method('consume')
            ->willThrowException($exception);

        $this->messageMock
            ->expects($this->once())
            ->method('setError')
            ->with($message)
            ->willReturn($this->messageMock);

        $this->consumerMock
            ->expects($this->once())
            ->method('getMetadata')
            ->with(false, null, 120e3)
            ->willReturn($metadata);

        $this->consumerMock
            ->expects($this->once())
            ->method('getSubscription')
            ->willReturn($topics);

        Log::shouldReceive('error')
            ->with($message, [
                'exception' => $exception,
                'metadata' => $exception,
                'topics' => $topics,
            ]);

        $response = $this->consumerService->consume(120000);

        $this->assertEquals($this->messageMock, $response);
    }
}
