<?php

declare(strict_types=1);

namespace App\Domain\User\Events;

use App\Domain\User\ValueObjects\UserId;
use App\Domain\Abstractions\AbstractDomainEvent;

final class UserRoleChanged extends AbstractDomainEvent
{
    public function __construct(
        UserId $userId,
        private string $oldRole,
        private string $newRole,
        private ?string $changedBy = null
    ) {
        parent::__construct($userId->toInt(), 'user_role_changed', [
            'old_role' => $oldRole,
            'new_role' => $newRole,
            'changed_by' => $changedBy,
        ]);
    }

    public static function create(
        UserId $userId,
        string $oldRole,
        string $newRole,
        ?string $changedBy = null
    ): self {
        return new self($userId, $oldRole, $newRole, $changedBy);
    }

    public function getUserId(): UserId
    {
        return UserId::fromInt($this->getAggregateId());
    }

    public function getOldRole(): string
    {
        return $this->oldRole;
    }

    public function getNewRole(): string
    {
        return $this->newRole;
    }

    public function getChangedBy(): ?string
    {
        return $this->changedBy;
    }
}