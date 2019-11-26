<?php

namespace OneFit\Events\Tests\Unit\Observers;

use PHPUnit\Framework\TestCase;
use OneFit\Events\Models\Topic;
use OneFit\Events\Models\Message;
use Illuminate\Support\Facades\Log;
use OneFit\Events\Services\ProducerService;
use OneFit\Events\Observers\CreatedObserver;
use PHPUnit\Framework\MockObject\MockObject;
use Illuminate\Contracts\Queue\QueueableEntity;

/**
 * Class CreatedObserverTest.
 */
class CreatedObserverTest extends TestCase
{
    /**
     * @var QueueableEntity|MockObject
     */
    private $entityMock;

    /**
     * @var ProducerService|MockObject
     */
    private $producerMock;

    /**
     * @var Message|MockObject
     */
    private $messageMock;

    /**
     * @var CreatedObserver
     */
    private $createdObserver;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->entityMock = $this->createMock(QueueableEntity::class);
        $this->producerMock = $this->createMock(ProducerService::class);
        $this->messageMock = $this->createMock(Message::class);
        $this->createdObserver = new CreatedObserver(function () {
            return $this->producerMock;
        }, $this->messageMock, Topic::MEMBER_DOMAIN);

        parent::setUp();
    }

    /** @test */
    public function can_observe_created()
    {
        $this->entityMock
            ->expects($this->once())
            ->method('getQueueableId')
            ->willReturn('2019');

        $this->entityMock
            ->expects($this->once())
            ->method('getQueueableConnection')
            ->willReturn('mysql');

        $this->messageMock
            ->expects($this->once())
            ->method('setEvent')
            ->with('created')
            ->willReturn($this->messageMock);

        $this->messageMock
            ->expects($this->once())
            ->method('setId')
            ->with('2019')
            ->willReturn($this->messageMock);

        $this->messageMock
            ->expects($this->once())
            ->method('setConnection')
            ->with('mysql')
            ->willReturn($this->messageMock);

        $this->messageMock
            ->expects($this->once())
            ->method('setPayload')
            ->with(json_encode($this->entityMock, JSON_FORCE_OBJECT))
            ->willReturn($this->messageMock);

        $this->producerMock
            ->expects($this->once())
            ->method('produce')
            ->with($this->isInstanceOf(Message::class), Topic::MEMBER_DOMAIN);

        call_user_func($this->createdObserver, $this->entityMock);
    }

    /** @test */
    public function will_fail_gracefully()
    {
        $this->entityMock
            ->expects($this->once())
            ->method('getQueueableId')
            ->willReturn('2019');

        $this->entityMock
            ->expects($this->once())
            ->method('getQueueableConnection')
            ->willReturn('mysql');

        $this->messageMock
            ->expects($this->once())
            ->method('setEvent')
            ->with('created')
            ->willReturn($this->messageMock);

        $this->messageMock
            ->expects($this->once())
            ->method('setId')
            ->with('2019')
            ->willReturn($this->messageMock);

        $this->messageMock
            ->expects($this->once())
            ->method('setConnection')
            ->with('mysql')
            ->willReturn($this->messageMock);

        $this->messageMock
            ->expects($this->once())
            ->method('setPayload')
            ->with(json_encode($this->entityMock, JSON_FORCE_OBJECT))
            ->willReturn($this->messageMock);

        $this->producerMock
            ->expects($this->once())
            ->method('produce')
            ->with($this->isInstanceOf(Message::class), Topic::MEMBER_DOMAIN)
            ->willThrowException(new \Exception('something went wrong'));

        Log::shouldReceive('error')->once();

        call_user_func($this->createdObserver, $this->entityMock);
    }
}
