<?php

namespace OneFit\Events\Tests\Unit\Services;

use Illuminate\Support\Facades\Log;
use RdKafka\KafkaConsumer;
use PHPUnit\Framework\TestCase;
use OneFit\Events\Models\Message;
use RdKafka\Message as KafkaMessage;
use OneFit\Events\Services\ConsumerService;
use PHPUnit\Framework\MockObject\MockClass;
use RdKafka\Metadata;

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

        $this->consumerService = new ConsumerService($this->consumerMock, $this->messageMock);
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
