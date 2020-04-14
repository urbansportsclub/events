<?php

namespace OneFit\Events\Tests\Unit\Observers;

use JsonSerializable;
use PHPUnit\Framework\TestCase;
use OneFit\Events\Models\Message;
use Illuminate\Support\Facades\Log;
use OneFit\Events\Services\ProducerService;
use OneFit\Events\Observers\DeletedObserver;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class DeletedObserverTest.
 */
class DeletedObserverTest extends TestCase
{
    /**
     * @var JsonSerializable|MockObject
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
     * @var DeletedObserver
     */
    private $deletedObserver;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->entityMock = $this->createMock(JsonSerializable::class);
        $this->producerMock = $this->createMock(ProducerService::class);
        $this->messageMock = $this->createMock(Message::class);
        $this->deletedObserver = new DeletedObserver(function () {
            return $this->producerMock;
        }, $this->messageMock, 'member_domain');

        parent::setUp();
    }

    /** @test */
    public function can_observe_deleted()
    {
        $payload = ['event' => 'data'];

        $this->entityMock
            ->expects($this->once())
            ->method('jsonSerialize')
            ->willReturn($payload);

        $this->messageMock
            ->expects($this->once())
            ->method('setEvent')
            ->with('deleted')
            ->willReturn($this->messageMock);

        $this->messageMock
            ->expects($this->once())
            ->method('setPayload')
            ->with($payload)
            ->willReturn($this->messageMock);

        $this->producerMock
            ->expects($this->once())
            ->method('produce')
            ->with($this->isInstanceOf(Message::class), 'member_domain');

        call_user_func($this->deletedObserver, $this->entityMock);
    }

    /** @test */
    public function will_fail_gracefully()
    {
        $payload = ['event' => 'data'];

        $this->entityMock
            ->expects($this->once())
            ->method('jsonSerialize')
            ->willReturn($payload);

        $this->messageMock
            ->expects($this->once())
            ->method('setEvent')
            ->with('deleted')
            ->willReturn($this->messageMock);

        $this->messageMock
            ->expects($this->once())
            ->method('setPayload')
            ->with($payload)
            ->willReturn($this->messageMock);

        $this->producerMock
            ->expects($this->once())
            ->method('produce')
            ->with($this->isInstanceOf(Message::class), 'member_domain')
            ->willThrowException(new \Exception('something went wrong'));

        Log::shouldReceive('error')->once();

        call_user_func($this->deletedObserver, $this->entityMock);
    }
}
