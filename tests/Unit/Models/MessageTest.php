<?php

namespace Tests\Unit\Models;

use JsonSerializable;
use PHPUnit\Framework\TestCase;
use OneFit\Events\Models\Message;

/**
 * Class MessageTest.
 */
class MessageTest extends TestCase
{
    /** @test */
    public function can_create_message()
    {
        $event = 'MyEventClass';
        $payload = ['data about the event'];
        $message = new Message($event, $payload);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertInstanceOf(JsonSerializable::class, $message);
        $this->assertSame($event, $message->getEvent());
        $this->assertSame($payload, $message->getPayload());
        $this->assertSame(['event' => $event, 'payload' => $payload], $message->jsonSerialize());
    }
}
