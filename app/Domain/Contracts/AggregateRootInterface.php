<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

interface AggregateRootInterface extends EntityInterface
{
    /**
     * Get all domain events that have been recorded.
     *
     * @return array<\App\Domain\Contracts\DomainEventInterface>
     */
    public function getDomainEvents(): array;

    /**
     * Clear all recorded domain events.
     */
    public function clearDomainEvents(): void;

    /**
     * Record a domain event.
     */
    public function recordDomainEvent(\App\Domain\Contracts\DomainEventInterface $event): void;
}