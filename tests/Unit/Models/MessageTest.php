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
        $event = 'my-event';
        $payload = ['event' => 'data'];
        $message = new Message();

        $message->setType($type)
            ->setSource('api')
            ->setEvent($event)
            ->setPayload($payload);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertInstanceOf(JsonSerializable::class, $message);
        $this->assertSame($event, $message->getEvent());
        $this->assertSame($type, $message->getType());
        $this->assertSame('api', $message->getSource());
        $this->assertSame($payload, $message->getPayload());
        $this->assertSame([
            'type' => $type,
            'event' => $event,
            'source' => 'api',
            'payload' => $payload,
        ], $message->jsonSerialize());
        $this->assertFalse($message->hasError());
        $this->assertNull($message->getError());
    }

    /** @test */
    public function can_hydrate_message()
    {
        $data = [
            'type' => 'check_in',
            'event' => 'my-event',
            'payload' => ['event' => 'data'],
            'source' => 'api',
        ];

        $message = new Message();
        $message->hydrate($data);

        $this->assertSame($data['event'], $message->getEvent());
        $this->assertSame($data['type'], $message->getType());
        $this->assertSame($data['source'], $message->getSource());
        $this->assertSame($data['payload'], $message->getPayload());
        $this->assertSame($data, $message->getOriginal());
        $this->assertFalse($message->hasError());
        $this->assertNull($message->getError());
    }

    /** @test */
    public function can_verify_signature()
    {
        $message = new Message();
        $salt = 'secret-salt';

        $message->setSalt($salt);

        $this->assertSame(sha1(json_encode($message, JSON_FORCE_OBJECT).$salt), $message->getSignature());
        $this->assertTrue($message->hasValidSignature(sha1(json_encode($message, JSON_FORCE_OBJECT).$salt)));
    }

    /** @test */
    public function can_set_error()
    {
        $message = new Message();
        $error = 'something went wrong';

        $message->setError($error);

        $this->assertTrue($message->hasError());
        $this->assertSame($error, $message->getError());
    }
}
