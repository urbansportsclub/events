<?php

namespace OneFit\Events\Models;

use JsonSerializable;

/**
 * Class Message.
 */
class Message implements JsonSerializable
{
    /**
     * @var string|null
     */
    private $type;

    /**
     * @var string|null
     */
    private $event;

    /**
     * @var string|null
     */
    private $source;

    /**
     * @var array|null
     */
    private $payload;

    /**
     * @var string|null
     */
    private $salt;

    /**
     * @var string|null
     */
    private $error;

    /**
     * @var array
     */
    private $original = [];

    /**
     * @return string|null
     */
    public function getSource(): ?string
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
     * @return string|null
     */
    public function getType(): ?string
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
     * @return array|null
     */
    public function getPayload(): ?array
    {
        return $this->payload;
    }

    /**
     * @param  array   $payload
     * @return Message
     */
    public function setPayload(array $payload): self
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
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * @param  string  $error
     * @return Message
     */
    public function setError(string $error): self
    {
        $this->error = $error;

        return $this;
    }

    /**
     * @return array
     */
    public function getOriginal(): array
    {
        return $this->original;
    }

    /**
     * @param  array   $original
     * @return Message
     */
    public function setOriginal(array $original): self
    {
        $this->original = $original;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return ! is_null($this->error);
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
            'type' => $this->type,
            'event' => $this->event,
            'source' => $this->source,
            'payload' => $this->payload,
        ];
    }

    /**
     * @param  array   $data
     * @return Message
     */
    public function hydrate(array $data): self
    {
        isset($data['type']) && $this->setType($data['type']);
        isset($data['event']) && $this->setEvent($data['event']);
        isset($data['source']) && $this->setSource($data['source']);
        isset($data['payload']) && $this->setPayload($data['payload']);
        ! empty($data) && $this->setOriginal($data);

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
