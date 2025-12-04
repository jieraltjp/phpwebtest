<?php

declare(strict_types=1);

namespace App\Domain\Order\ValueObjects;

use App\Domain\Abstractions\AbstractValueObject;

final class OrderStatus extends AbstractValueObject
{
    public const PENDING = 'pending';
    public const CONFIRMED = 'confirmed';
    public const PROCESSING = 'processing';
    public const SHIPPED = 'shipped';
    public const DELIVERED = 'delivered';
    public const CANCELLED = 'cancelled';
    public const REFUNDED = 'refunded';
    public const ON_HOLD = 'on_hold';

    private const VALID_STATUSES = [
        self::PENDING,
        self::CONFIRMED,
        self::PROCESSING,
        self::SHIPPED,
        self::DELIVERED,
        self::CANCELLED,
        self::REFUNDED,
        self::ON_HOLD,
    ];

    private const STATUS_FLOW = [
        self::PENDING => [self::CONFIRMED, self::CANCELLED],
        self::CONFIRMED => [self::PROCESSING, self::CANCELLED, self::ON_HOLD],
        self::PROCESSING => [self::SHIPPED, self::CANCELLED, self::ON_HOLD],
        self::SHIPPED => [self::DELIVERED],
        self::DELIVERED => [self::REFUNDED],
        self::CANCELLED => [],
        self::REFUNDED => [],
        self::ON_HOLD => [self::PROCESSING, self::CANCELLED],
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

    public static function confirmed(): self
    {
        return new self(self::CONFIRMED);
    }

    public static function processing(): self
    {
        return new self(self::PROCESSING);
    }

    public static function shipped(): self
    {
        return new self(self::SHIPPED);
    }

    public static function delivered(): self
    {
        return new self(self::DELIVERED);
    }

    public static function cancelled(): self
    {
        return new self(self::CANCELLED);
    }

    public static function refunded(): self
    {
        return new self(self::REFUNDED);
    }

    public static function onHold(): self
    {
        return new self(self::ON_HOLD);
    }

    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }

    public function isConfirmed(): bool
    {
        return $this->value === self::CONFIRMED;
    }

    public function isProcessing(): bool
    {
        return $this->value === self::PROCESSING;
    }

    public function isShipped(): bool
    {
        return $this->value === self::SHIPPED;
    }

    public function isDelivered(): bool
    {
        return $this->value === self::DELIVERED;
    }

    public function isCancelled(): bool
    {
        return $this->value === self::CANCELLED;
    }

    public function isRefunded(): bool
    {
        return $this->value === self::REFUNDED;
    }

    public function isOnHold(): bool
    {
        return $this->value === self::ON_HOLD;
    }

    public function isActive(): bool
    {
        return in_array($this->value, [self::PENDING, self::CONFIRMED, self::PROCESSING, self::SHIPPED, self::ON_HOLD], true);
    }

    public function isCompleted(): bool
    {
        return $this->value === self::DELIVERED;
    }

    public function isTerminated(): bool
    {
        return in_array($this->value, [self::CANCELLED, self::REFUNDED], true);
    }

    public function canTransitionTo(OrderStatus $newStatus): bool
    {
        return in_array($newStatus->getValue(), self::STATUS_FLOW[$this->value] ?? [], true);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->value, [self::PENDING, self::CONFIRMED, self::PROCESSING, self::ON_HOLD], true);
    }

    public function canBeRefunded(): bool
    {
        return $this->value === self::DELIVERED;
    }

    public function canBeModified(): bool
    {
        return in_array($this->value, [self::PENDING, self::CONFIRMED], true);
    }

    public function requiresShipping(): bool
    {
        return in_array($this->value, [self::CONFIRMED, self::PROCESSING, self::SHIPPED], true);
    }

    public function getDisplayName(): string
    {
        return match ($this->value) {
            self::PENDING => '待处理',
            self::CONFIRMED => '已确认',
            self::PROCESSING => '处理中',
            self::SHIPPED => '已发货',
            self::DELIVERED => '已送达',
            self::CANCELLED => '已取消',
            self::REFUNDED => '已退款',
            self::ON_HOLD => '暂停',
            default => '未知状态',
        };
    }

    public function getColorClass(): string
    {
        return match ($this->value) {
            self::PENDING => 'text-yellow-600',
            self::CONFIRMED => 'text-blue-600',
            self::PROCESSING => 'text-indigo-600',
            self::SHIPPED => 'text-purple-600',
            self::DELIVERED => 'text-green-600',
            self::CANCELLED => 'text-red-600',
            self::REFUNDED => 'text-gray-600',
            self::ON_HOLD => 'text-orange-600',
            default => 'text-gray-400',
        };
    }

    public function getBadgeClass(): string
    {
        return match ($this->value) {
            self::PENDING => 'bg-yellow-100 text-yellow-800',
            self::CONFIRMED => 'bg-blue-100 text-blue-800',
            self::PROCESSING => 'bg-indigo-100 text-indigo-800',
            self::SHIPPED => 'bg-purple-100 text-purple-800',
            self::DELIVERED => 'bg-green-100 text-green-800',
            self::CANCELLED => 'bg-red-100 text-red-800',
            self::REFUNDED => 'bg-gray-100 text-gray-800',
            self::ON_HOLD => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getPriority(): int
    {
        return match ($this->value) {
            self::PENDING => 1, // Highest priority
            self::ON_HOLD => 2,
            self::CONFIRMED => 3,
            self::PROCESSING => 4,
            self::SHIPPED => 5,
            self::DELIVERED => 6,
            self::CANCELLED => 7,
            self::REFUNDED => 8, // Lowest priority
            default => 9,
        };
    }

    public function getAllowedTransitions(): array
    {
        return self::STATUS_FLOW[$this->value] ?? [];
    }

    protected function getValue(): mixed
    {
        return $this->value;
    }

    private function validate(string $value): void
    {
        if (!in_array($value, self::VALID_STATUSES, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid order status "%s". Valid statuses are: %s',
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