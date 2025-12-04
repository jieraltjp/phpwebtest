<?php

declare(strict_types=1);

namespace App\Domain\Inquiry\ValueObjects;

use App\Domain\Abstractions\AbstractValueObject;

final class InquiryStatus extends AbstractValueObject
{
    public const PENDING = 'pending';
    public const QUOTED = 'quoted';
    public const ACCEPTED = 'accepted';
    public const REJECTED = 'rejected';
    public const EXPIRED = 'expired';
    public const WITHDRAWN = 'withdrawn';

    private const VALID_STATUSES = [
        self::PENDING,
        self::QUOTED,
        self::ACCEPTED,
        self::REJECTED,
        self::EXPIRED,
        self::WITHDRAWN,
    ];

    private const STATUS_FLOW = [
        self::PENDING => [self::QUOTED, self::REJECTED, self::WITHDRAWN],
        self::QUOTED => [self::ACCEPTED, self::REJECTED, self::EXPIRED, self::WITHDRAWN],
        self::ACCEPTED => [],
        self::REJECTED => [],
        self::EXPIRED => [],
        self::WITHDRAWN => [],
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

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function quoted(): self
    {
        return new self(self::QUOTED);
    }

    public static function accepted(): self
    {
        return new self(self::ACCEPTED);
    }

    public static function rejected(): self
    {
        return new self(self::REJECTED);
    }

    public static function expired(): self
    {
        return new self(self::EXPIRED);
    }

    public static function withdrawn(): self
    {
        return new self(self::WITHDRAWN);
    }

    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }

    public function isQuoted(): bool
    {
        return $this->value === self::QUOTED;
    }

    public function isAccepted(): bool
    {
        return $this->value === self::ACCEPTED;
    }

    public function isRejected(): bool
    {
        return $this->value === self::REJECTED;
    }

    public function isExpired(): bool
    {
        return $this->value === self::EXPIRED;
    }

    public function isWithdrawn(): bool
    {
        return $this->value === self::WITHDRAWN;
    }

    public function isActive(): bool
    {
        return in_array($this->value, [self::PENDING, self::QUOTED], true);
    }

    public function isCompleted(): bool
    {
        return in_array($this->value, [self::ACCEPTED, self::REJECTED, self::EXPIRED, self::WITHDRAWN], true);
    }

    public function canTransitionTo(InquiryStatus $newStatus): bool
    {
        return in_array($newStatus->getValue(), self::STATUS_FLOW[$this->value] ?? [], true);
    }

    public function canBeQuoted(): bool
    {
        return $this->value === self::PENDING;
    }

    public function canBeAccepted(): bool
    {
        return $this->value === self::QUOTED;
    }

    public function canBeRejected(): bool
    {
        return in_array($this->value, [self::PENDING, self::QUOTED], true);
    }

    public function canBeWithdrawn(): bool
    {
        return in_array($this->value, [self::PENDING, self::QUOTED], true);
    }

    public function getDisplayName(): string
    {
        return match ($this->value) {
            self::PENDING => '待处理',
            self::QUOTED => '已报价',
            self::ACCEPTED => '已接受',
            self::REJECTED => '已拒绝',
            self::EXPIRED => '已过期',
            self::WITHDRAWN => '已撤回',
            default => '未知状态',
        };
    }

    public function getColorClass(): string
    {
        return match ($this->value) {
            self::PENDING => 'text-yellow-600',
            self::QUOTED => 'text-blue-600',
            self::ACCEPTED => 'text-green-600',
            self::REJECTED => 'text-red-600',
            self::EXPIRED => 'text-gray-600',
            self::WITHDRAWN => 'text-orange-600',
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
                'Invalid inquiry status "%s". Valid statuses are: %s',
                $value,
                implode(', ', self::VALID_STATUSES)
            ));
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }
}