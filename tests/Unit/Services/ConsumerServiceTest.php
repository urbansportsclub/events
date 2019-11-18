<?php

namespace Tests\Unit\Services;

use RdKafka\Message;
use RdKafka\KafkaConsumer;
use PHPUnit\Framework\TestCase;
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
        $this->messageMock = $this->createMock(Message::class);

        $this->consumerService = new ConsumerService($this->consumerMock);
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
            ->willReturn($this->messageMock);

        $response = $this->consumerService->consume(120000);

        $this->assertSame($this->messageMock, $response);
    }
}
