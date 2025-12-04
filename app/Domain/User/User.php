<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\User\ValueObjects\UserId;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\Username;
use App\Domain\User\ValueObjects\UserRole;
use App\Domain\User\ValueObjects\UserStatus;
use App\Domain\User\Events\UserRegistered;
use App\Domain\User\Events\UserStatusChanged;
use App\Domain\User\Events\UserRoleChanged;
use App\Domain\Abstractions\AbstractAggregateRoot;

final class User extends AbstractAggregateRoot
{
    private UserId $id;
    private Username $username;
    private Email $email;
    private string $passwordHash;
    private UserRole $role;
    private UserStatus $status;
    private \DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $updatedAt;
    private ?\DateTimeImmutable $lastLoginAt;
    private ?string $rememberToken;

    private function __construct(
        UserId $id,
        Username $username,
        Email $email,
        string $passwordHash,
        UserRole $role,
        UserStatus $status
    ) {
        parent::__construct($id->toInt());
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->role = $role;
        $this->status = $status;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = null;
        $this->lastLoginAt = null;
        $this->rememberToken = null;
    }

    public static function register(
        UserId $id,
        Username $username,
        Email $email,
        string $passwordHash,
        UserRole $role
    ): self {
        $user = new self(
            $id,
            $username,
            $email,
            $passwordHash,
            $role,
            UserStatus::pendingVerification()
        );

        $user->recordDomainEvent(
            UserRegistered::create(
                $id,
                $username->toString(),
                $email->toString(),
                $role->getValue()
            )
        );

        return $user;
    }

    public static function createExisting(
        UserId $id,
        Username $username,
        Email $email,
        string $passwordHash,
        UserRole $role,
        UserStatus $status,
        \DateTimeImmutable $createdAt,
        ?\DateTimeImmutable $updatedAt = null,
        ?\DateTimeImmutable $lastLoginAt = null,
        ?string $rememberToken = null
    ): self {
        $user = new self($id, $username, $email, $passwordHash, $role, $status);
        $user->createdAt = $createdAt;
        $user->updatedAt = $updatedAt;
        $user->lastLoginAt = $lastLoginAt;
        $user->rememberToken = $rememberToken;

        return $user;
    }

    public function getId(): UserId
    {
        return $this->id;
    }

    public function getUsername(): Username
    {
        return $this->username;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function getStatus(): UserStatus
    {
        return $this->status;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function getRememberToken(): ?string
    {
        return $this->rememberToken;
    }

    public function updatePassword(string $newPasswordHash): void
    {
        if ($this->passwordHash === $newPasswordHash) {
            return;
        }

        $this->passwordHash = $newPasswordHash;
        $this->markAsUpdated();
    }

    public function changeStatus(UserStatus $newStatus, ?string $reason = null): void
    {
        if ($this->status->equals($newStatus)) {
            return;
        }

        $oldStatus = $this->status->getValue();
        $this->status = $newStatus;
        $this->markAsUpdated();

        $this->recordDomainEvent(
            UserStatusChanged::create(
                $this->id,
                $oldStatus,
                $newStatus->getValue(),
                $reason
            )
        );
    }

    public function changeRole(UserRole $newRole, ?string $changedBy = null): void
    {
        if ($this->role->equals($newRole)) {
            return;
        }

        $oldRole = $this->role->getValue();
        $this->role = $newRole;
        $this->markAsUpdated();

        $this->recordDomainEvent(
            UserRoleChanged::create(
                $this->id,
                $oldRole,
                $newRole->getValue(),
                $changedBy
            )
        );
    }

    public function updateEmail(Email $newEmail): void
    {
        if ($this->email->equals($newEmail)) {
            return;
        }

        $this->email = $newEmail;
        $this->markAsUpdated();
    }

    public function updateUsername(Username $newUsername): void
    {
        if ($this->username->equals($newUsername)) {
            return;
        }

        $this->username = $newUsername;
        $this->markAsUpdated();
    }

    public function recordLogin(): void
    {
        $this->lastLoginAt = new \DateTimeImmutable();
        $this->markAsUpdated();
    }

    public function updateRememberToken(?string $token): void
    {
        $this->rememberToken = $token;
        $this->markAsUpdated();
    }

    public function canLogin(): bool
    {
        return $this->status->canLogin();
    }

    public function canPlaceOrders(): bool
    {
        return $this->status->canPlaceOrders();
    }

    public function canManageOrders(): bool
    {
        return $this->role->canManageOrders() && $this->status->isActive();
    }

    public function canManageProducts(): bool
    {
        return $this->role->canManageProducts() && $this->status->isActive();
    }

    public function canViewReports(): bool
    {
        return $this->role->canViewReports() && $this->status->isActive();
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isPendingVerification(): bool
    {
        return $this->status->isPendingVerification();
    }

    public function isSuspended(): bool
    {
        return $this->status->isSuspended();
    }

    public function isAdmin(): bool
    {
        return $this->role->isAdmin();
    }

    public function isCustomer(): bool
    {
        return $this->role->isCustomer();
    }

    public function isSupplier(): bool
    {
        return $this->role->isSupplier();
    }

    private function markAsUpdated(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}