<?php

declare(strict_types=1);

namespace App\Domain\Abstractions;

use App\Domain\Contracts\DomainEventInterface;
use DateTimeImmutable;

abstract class AbstractDomainEvent implements DomainEventInterface
{
    private string $eventId;
    private DateTimeImmutable $occurredAt;

    protected function __construct(
        private mixed $aggregateId,
        private string $eventName,
        private array $payload = []
    ) {
        $this->eventId = $this->generateEventId();
        $this->occurredAt = new DateTimeImmutable();
    }

    public function getEventId(): string
    {
        return $this->eventId;
    }

    public function getAggregateId(): mixed
    {
        return $this->aggregateId;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    private function generateEventId(): string
    {
        return uniqid('event_', true);
    }

    /**
     * Create a new domain event instance.
     */
    protected static function create(
        mixed $aggregateId,
        string $eventName,
        array $payload = []
    ): static {
        return new static($aggregateId, $eventName, $payload);
    }
}