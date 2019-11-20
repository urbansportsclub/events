<?php

namespace OneFit\Events\Tests\Unit\Models;

use JsonSerializable;
use PHPUnit\Framework\TestCase;
use OneFit\Events\Models\Source;
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
        $connection = 'mysql';
        $event = 'MyEventClass';
        $payload = 'data about the event';
        $message = new Message($type, Source::API, 'secret-salt');

        $message->setEvent($event)
            ->setId($uuid)
            ->setConnection($connection)
            ->setPayload($payload);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertInstanceOf(JsonSerializable::class, $message);
        $this->assertSame($event, $message->getEvent());
        $this->assertSame($type, $message->getType());
        $this->assertSame(Source::API, $message->getSource());
        $this->assertSame($connection, $message->getConnection());
        $this->assertSame($payload, $message->getPayload());
        $this->assertSame([
            'event' => $event,
            'type' => $type,
            'id' => $uuid,
            'source' => Source::API,
            'connection' => $connection,
            'payload' => $payload,
        ], $message->jsonSerialize());
        $this->assertSame(sha1(json_encode($message, JSON_FORCE_OBJECT).'secret-salt'), $message->getSignature());
    }
}
