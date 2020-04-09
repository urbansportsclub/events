<?php

namespace OneFit\Events\Tests\Unit\Services;

use FlixTech\AvroSerializer\Objects\RecordSerializer;
use RdKafka\Metadata;
use RdKafka\KafkaConsumer;
use PHPUnit\Framework\TestCase;
use OneFit\Events\Models\Message;
use Illuminate\Support\Facades\Log;
use RdKafka\Message as KafkaMessage;
use OneFit\Events\Services\ConsumerService;
use PHPUnit\Framework\MockObject\MockClass;

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

        $serializer = function () {
            return $this->serializerMock;
        };

        $this->consumerService = new ConsumerService($this->consumerMock, $this->messageMock, $serializer, []);
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
            ->method('getMetadata')
            ->with(false, null, 60e3)
            ->willReturn($topics);

        Log::shouldReceive('error')
            ->with($message, [
                'exception' => $exception,
                'metadata' => $exception,
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
}
