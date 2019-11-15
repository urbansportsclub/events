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
        $type = 'created';
        $payload = ['data about the event'];
        $message = new Message($event, $type, $payload);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertInstanceOf(JsonSerializable::class, $message);
        $this->assertSame($event, $message->getEvent());
        $this->assertSame($type, $message->getType());
        $this->assertSame($payload, $message->getPayload());
        $this->assertSame(['event' => $event, 'type' => $type, 'payload' => $payload], $message->jsonSerialize());
    }
}
