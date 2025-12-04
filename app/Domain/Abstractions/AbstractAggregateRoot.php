<?php

declare(strict_types=1);

namespace App\Domain\Abstractions;

use App\Domain\Contracts\AggregateRootInterface;
use App\Domain\Contracts\DomainEventInterface;

abstract class AbstractAggregateRoot extends AbstractEntity implements AggregateRootInterface
{
    /** @var DomainEventInterface[] */
    private array $domainEvents = [];

    protected function __construct(mixed $id)
    {
        parent::__construct($id);
    }

    public function getDomainEvents(): array
    {
        return $this->domainEvents;
    }

    public function clearDomainEvents(): void
    {
        $this->domainEvents = [];
    }

    public function recordDomainEvent(DomainEventInterface $event): void
    {
        $this->domainEvents[] = $event;
    }
}