<?php

declare(strict_types=1);

namespace App\Domain\User\Events;

use App\Domain\User\ValueObjects\UserId;
use App\Domain\Abstractions\AbstractDomainEvent;

final class UserRegistered extends AbstractDomainEvent
{
    public function __construct(
        UserId $userId,
        private string $username,
        private string $email,
        private string $role
    ) {
        parent::__construct($userId->toInt(), 'user_registered', [
            'username' => $username,
            'email' => $email,
            'role' => $role,
        ]);
    }

    public static function create(
        UserId $userId,
        string $username,
        string $email,
        string $role
    ): self {
        return new self($userId, $username, $email, $role);
    }

    public function getUserId(): UserId
    {
        return UserId::fromInt($this->getAggregateId());
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRole(): string
    {
        return $this->role;
    }
}