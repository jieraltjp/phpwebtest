<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

use App\Domain\Abstractions\AbstractValueObject;

final class UserStatus extends AbstractValueObject
{
    public const ACTIVE = 'active';
    public const INACTIVE = 'inactive';
    public const SUSPENDED = 'suspended';
    public const PENDING_VERIFICATION = 'pending_verification';

    private const VALID_STATUSES = [
        self::ACTIVE,
        self::INACTIVE,
        self::SUSPENDED,
        self::PENDING_VERIFICATION,
    ];

    private string $value;

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function active(): self
    {
        return new self(self::ACTIVE);
    }

    public static function inactive(): self
    {
        return new self(self::INACTIVE);
    }

    public static function suspended(): self
    {
        return new self(self::SUSPENDED);
    }

    public static function pendingVerification(): self
    {
        return new self(self::PENDING_VERIFICATION);
    }

    public function isActive(): bool
    {
        return $this->value === self::ACTIVE;
    }

    public function isInactive(): bool
    {
        return $this->value === self::INACTIVE;
    }

    public function isSuspended(): bool
    {
        return $this->value === self::SUSPENDED;
    }

    public function isPendingVerification(): bool
    {
        return $this->value === self::PENDING_VERIFICATION;
    }

    public function canLogin(): bool
    {
        return $this->isActive();
    }

    public function canPlaceOrders(): bool
    {
        return $this->isActive();
    }

    public function canManageAccount(): bool
    {
        return $this->isActive() || $this->isPendingVerification();
    }

    public function getDisplayName(): string
    {
        return match ($this->value) {
            self::ACTIVE => '活跃',
            self::INACTIVE => '非活跃',
            self::SUSPENDED => '已暂停',
            self::PENDING_VERIFICATION => '待验证',
            default => '未知状态',
        };
    }

    public function getColorClass(): string
    {
        return match ($this->value) {
            self::ACTIVE => 'text-green-600',
            self::INACTIVE => 'text-gray-500',
            self::SUSPENDED => 'text-red-600',
            self::PENDING_VERIFICATION => 'text-yellow-600',
            default => 'text-gray-400',
        };
    }

    protected function getValue(): mixed
    {
        return $this->value;
    }

    private function validate(string $value): void
    {
        if (!in_array($value, self::VALID_STATUSES, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid status "%s". Valid statuses are: %s',
                $value,
                implode(', ', self::VALID_STATUSES)
            ));
        }
    }
}