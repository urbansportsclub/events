<?php

namespace OneFit\Events\Tests\Unit\Services;

use RdKafka\Producer;
use RdKafka\ProducerTopic;
use OneFit\Events\Models\Topic;
use PHPUnit\Framework\TestCase;
use OneFit\Events\Models\Message;
use OneFit\Events\Services\ProducerService;
use PHPUnit\Framework\MockObject\MockClass;

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

        $this->producerService = new ProducerService($this->producerMock, 3);

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

        $this->producerMock
            ->expects($this->once())
            ->method('flush')
            ->with(1000)
            ->willReturn(RD_KAFKA_RESP_ERR_NO_ERROR);

        $this->producerService->produce($this->messageMock, Topic::MEMBER_DOMAIN);
    }

    /** @test */
    public function will_try_to_flush_multiple_times()
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

        $this->producerMock
            ->expects($this->exactly(2))
            ->method('flush')
            ->with(1000)
            ->willReturnOnConsecutiveCalls(
                RD_KAFKA_RESP_ERR_UNKNOWN,
                RD_KAFKA_RESP_ERR_NO_ERROR
            );

        $this->producerService->produce($this->messageMock, Topic::MEMBER_DOMAIN);
    }
}
