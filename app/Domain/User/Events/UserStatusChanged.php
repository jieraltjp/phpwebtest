<?php

declare(strict_types=1);

namespace App\Domain\User\Events;

use App\Domain\User\ValueObjects\UserId;
use App\Domain\Abstractions\AbstractDomainEvent;

final class UserStatusChanged extends AbstractDomainEvent
{
    public function __construct(
        UserId $userId,
        private string $oldStatus,
        private string $newStatus,
        private ?string $reason = null
    ) {
        parent::__construct($userId->toInt(), 'user_status_changed', [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason' => $reason,
        ]);
    }

    public static function create(
        UserId $userId,
        string $oldStatus,
        string $newStatus,
        ?string $reason = null
    ): self {
        return new self($userId, $oldStatus, $newStatus, $reason);
    }

    public function getUserId(): UserId
    {
        return UserId::fromInt($this->getAggregateId());
    }

    public function getOldStatus(): string
    {
        return $this->oldStatus;
    }

    public function getNewStatus(): string
    {
        return $this->newStatus;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }
}