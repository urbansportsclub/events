<?php

namespace OneFit\Events\Tests\Unit\Models;

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
        $type = 'check_in';
        $uuid = 'uuid';
        $source = 'mysql';
        $event = 'MyEventClass';
        $payload = 'data about the event';
        $message = new Message($type);

        $message->setEvent($event)
            ->setId($uuid)
            ->setSource($source)
            ->setPayload($payload);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertInstanceOf(JsonSerializable::class, $message);
        $this->assertSame($event, $message->getEvent());
        $this->assertSame($type, $message->getType());
        $this->assertSame($payload, $message->getPayload());
        $this->assertSame([
            'event' => $event,
            'type' => $type,
            'id' => $uuid,
            'source' => $source,
            'payload' => $payload,
        ], $message->jsonSerialize());
    }
}
