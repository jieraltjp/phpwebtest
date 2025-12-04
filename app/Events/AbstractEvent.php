<?php

namespace App\Events;

use App\Events\Contracts\EventInterface;
use DateTime;
use Ramsey\Uuid\Uuid;

abstract class AbstractEvent implements EventInterface
{
    protected string $id;
    protected DateTime $timestamp;
    protected array $data;
    protected array $metadata;
    protected bool $async;
    protected int $priority;

    public function __construct(array $data = [], array $metadata = [], bool $async = false, int $priority = 0)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->timestamp = new DateTime();
        $this->data = $data;
        $this->metadata = $metadata;
        $this->async = $async;
        $this->priority = $priority;
    }

    public function getName(): string
    {
        return static::class;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getTimestamp(): DateTime
    {
        return $this->timestamp;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function shouldProcessAsync(): bool
    {
        return $this->async;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function serialize(): string
    {
        return serialize([
            'class' => static::class,
            'id' => $this->id,
            'timestamp' => $this->timestamp->format('Y-m-d H:i:s'),
            'data' => $this->data,
            'metadata' => $this->metadata,
            'async' => $this->async,
            'priority' => $this->priority
        ]);
    }

    public static function deserialize(string $data): self
    {
        $payload = unserialize($data);
        $class = $payload['class'];
        
        $event = new $class($payload['data'], $payload['metadata'], $payload['async'], $payload['priority']);
        $event->id = $payload['id'];
        $event->timestamp = new DateTime($payload['timestamp']);
        
        return $event;
    }

    /**
     * 获取特定数据字段
     */
    protected function getDataField(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * 设置数据字段
     */
    protected function setDataField(string $key, mixed $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * 获取元数据字段
     */
    protected function getMetadataField(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * 设置元数据字段
     */
    protected function setMetadataField(string $key, mixed $value): self
    {
        $this->metadata[$key] = $value;
        return $this;
    }
}