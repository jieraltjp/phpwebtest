<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

interface DomainEventInterface
{
    /**
     * Get the event ID.
     */
    public function getEventId(): string;

    /**
     * Get the aggregate root ID that triggered this event.
     */
    public function getAggregateId(): mixed;

    /**
     * Get the event name.
     */
    public function getEventName(): string;

    /**
     * Get the event payload.
     */
    public function getPayload(): array;

    /**
     * Get the occurred at timestamp.
     */
    public function getOccurredAt(): \DateTimeImmutable;
}