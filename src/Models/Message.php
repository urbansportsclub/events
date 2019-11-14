<?php

namespace OneFit\Events\Models;

use JsonSerializable;

class Message implements JsonSerializable
{
    /**
     * @var string
     */
    private $event;

    /**
     * @var array
     */
    private $payload;

    /**
     * Message constructor.
     * @param string $event
     * @param array  $payload
     */
    public function __construct(string $event, array $payload)
    {
        $this->event = $event;
        $this->payload = $payload;
    }

    /**
     * @return string
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * Specify data which should be serialized to JSON.
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *               which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'event' => $this->event,
            'payload' => $this->payload,
        ];
    }
}
