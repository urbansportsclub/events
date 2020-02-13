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
        $message = new Message();

        $message->setType($type)
            ->setSource(Source::API)
            ->setSalt('secret-salt')
            ->setEvent($event)
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
            'id' => $uuid,
            'type' => $type,
            'event' => $event,
            'source' => Source::API,
            'connection' => $connection,
            'payload' => $payload,
        ], $message->jsonSerialize());
        $this->assertSame(sha1(json_encode($message, JSON_FORCE_OBJECT).'secret-salt'), $message->getSignature());
    }

    /** @test */
    public function can_hydrate_message()
    {
        $data = [
            'type' => 'check_in',
            'id' => 'uuid',
            'connection' => 'mysql',
            'event' => 'MyEventClass',
            'payload' => 'data about the event',
            'source' => 'api',
        ];

        $message = new Message();
        $message->hydrate($data);

        $this->assertSame($data['event'], $message->getEvent());
        $this->assertSame($data['type'], $message->getType());
        $this->assertSame($data['source'], $message->getSource());
        $this->assertSame($data['connection'], $message->getConnection());
        $this->assertSame($data['payload'], $message->getPayload());
        $this->assertSame($data, $message->getOriginal());
    }
}
