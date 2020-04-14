<?php

namespace OneFit\Events\Tests\Unit\Observers;

use JsonSerializable;
use PHPUnit\Framework\TestCase;
use OneFit\Events\Models\Message;
use Illuminate\Support\Facades\Log;
use OneFit\Events\Observers\CustomObserver;
use OneFit\Events\Services\ProducerService;
use PHPUnit\Framework\MockObject\MockObject;

class CustomObserverTest extends TestCase
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
     * @var CustomObserver
     */
    private $customObserver;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->entityMock = $this->createMock(JsonSerializable::class);
        $this->producerMock = $this->createMock(ProducerService::class);
        $this->messageMock = $this->createMock(Message::class);
        $this->customObserver = new CustomObserver(function () {
            return $this->producerMock;
        }, $this->messageMock, 'notification_stream');

        parent::setUp();
    }

    /** @test */
    public function can_observe_custom()
    {
        $payload = ['event' => 'data'];

        $this->entityMock
            ->expects($this->once())
            ->method('jsonSerialize')
            ->willReturn($payload);

        $this->messageMock
            ->expects($this->once())
            ->method('setEvent')
            ->with('custom')
            ->willReturn($this->messageMock);

        $this->messageMock
            ->expects($this->once())
            ->method('setPayload')
            ->with($payload)
            ->willReturn($this->messageMock);

        $this->producerMock
            ->expects($this->once())
            ->method('produce')
            ->with($this->isInstanceOf(Message::class), 'notification_stream');

        call_user_func($this->customObserver, 'custom', [$this->entityMock]);
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
            ->with('custom')
            ->willReturn($this->messageMock);

        $this->messageMock
            ->expects($this->once())
            ->method('setPayload')
            ->with($payload)
            ->willReturn($this->messageMock);

        $this->producerMock
            ->expects($this->once())
            ->method('produce')
            ->with($this->isInstanceOf(Message::class), 'notification_stream')
            ->willThrowException(new \Exception('something went wrong'));

        Log::shouldReceive('error')->once();

        call_user_func($this->customObserver, 'custom', [$this->entityMock]);
    }
}
