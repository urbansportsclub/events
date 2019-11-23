<?php

namespace OneFit\Events\Models;

use JsonSerializable;

class Message implements JsonSerializable
{
    /**
     * @var string|null
     */
    private $event;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $connection;

    /**
     * @var string|null
     */
    private $payload;

    /**
     * @var string
     */
    private $salt;

    /**
     * Message constructor.
     * @param string $type
     * @param string $source
     * @param string $salt
     */
    public function __construct(string $type = '', string $source = '', string $salt = '')
    {
        $this->type = $type;
        $this->source = $source;
        $this->salt = $salt;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param  string  $id
     * @return Message
     */
    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param  string  $source
     * @return Message
     */
    public function setSource(string $source): self
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEvent(): ?string
    {
        return $this->event;
    }

    /**
     * @param  string  $event
     * @return Message
     */
    public function setEvent(string $event): self
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param  string  $type
     * @return Message
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getConnection(): ?string
    {
        return $this->connection;
    }

    /**
     * @param  string  $connection
     * @return Message
     */
    public function setConnection(string $connection): self
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPayload(): ?string
    {
        return $this->payload;
    }

    /**
     * @param  string  $payload
     * @return Message
     */
    public function setPayload(string $payload): self
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * @param  string  $salt
     * @return Message
     */
    public function setSalt(string $salt): self
    {
        $this->salt = $salt;

        return $this;
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
            'id' => $this->id,
            'source' => $this->source,
            'connection' => $this->connection,
            'payload' => $this->payload,
        ];
    }

    /**
     * @param  array   $data
     * @return Message
     */
    public function hydrate(array $data): self
    {
        isset($data['event']) && $this->setEvent($data['event']);
        isset($data['type']) && $this->setType($data['type']);
        isset($data['id']) && $this->setId($data['id']);
        isset($data['source']) && $this->setSource($data['source']);
        isset($data['connection']) && $this->setConnection($data['connection']);
        isset($data['payload']) && $this->setPayload($data['payload']);

        return $this;
    }

    /**
     * @return string
     */
    public function getSignature(): string
    {
        return sha1(json_encode($this, JSON_FORCE_OBJECT).$this->salt);
    }

    /**
     * @param  string $signature
     * @return bool
     */
    public function hasValidSignature(string $signature): bool
    {
        return $signature === $this->getSignature();
    }
}
