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
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $payload;

    /**
     * Message constructor.
     * @param string $event
     * @param string $type
     * @param array  $payload
     */
    public function __construct(string $event, string $type, array $payload)
    {
        $this->event = $event;
        $this->type = $type;
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
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
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
            'type' => $this->type,
            'payload' => $this->payload,
        ];
    }
}
