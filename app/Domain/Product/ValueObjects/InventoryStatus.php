<?php

declare(strict_types=1);

namespace App\Domain\Product\ValueObjects;

use App\Domain\Abstractions\AbstractValueObject;

final class InventoryStatus extends AbstractValueObject
{
    public const IN_STOCK = 'in_stock';
    public const LOW_STOCK = 'low_stock';
    public const OUT_OF_STOCK = 'out_of_stock';
    public const DISCONTINUED = 'discontinued';
    public const ON_ORDER = 'on_order';

    private const VALID_STATUSES = [
        self::IN_STOCK,
        self::LOW_STOCK,
        self::OUT_OF_STOCK,
        self::DISCONTINUED,
        self::ON_ORDER,
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

    public static function inStock(): self
    {
        return new self(self::IN_STOCK);
    }

    public static function lowStock(): self
    {
        return new self(self::LOW_STOCK);
    }

    public static function outOfStock(): self
    {
        return new self(self::OUT_OF_STOCK);
    }

    public static function discontinued(): self
    {
        return new self(self::DISCONTINUED);
    }

    public static function onOrder(): self
    {
        return new self(self::ON_ORDER);
    }

    public function isInStock(): bool
    {
        return $this->value === self::IN_STOCK;
    }

    public function isLowStock(): bool
    {
        return $this->value === self::LOW_STOCK;
    }

    public function isOutOfStock(): bool
    {
        return $this->value === self::OUT_OF_STOCK;
    }

    public function isDiscontinued(): bool
    {
        return $this->value === self::DISCONTINUED;
    }

    public function isOnOrder(): bool
    {
        return $this->value === self::ON_ORDER;
    }

    public function isAvailable(): bool
    {
        return in_array($this->value, [self::IN_STOCK, self::LOW_STOCK, self::ON_ORDER], true);
    }

    public function canBeOrdered(): bool
    {
        return in_array($this->value, [self::IN_STOCK, self::LOW_STOCK, self::ON_ORDER], true);
    }

    public function needsReorder(): bool
    {
        return in_array($this->value, [self::LOW_STOCK, self::OUT_OF_STOCK, self::ON_ORDER], true);
    }

    public function getDisplayName(): string
    {
        return match ($this->value) {
            self::IN_STOCK => '有库存',
            self::LOW_STOCK => '库存不足',
            self::OUT_OF_STOCK => '缺货',
            self::DISCONTINUED => '已停产',
            self::ON_ORDER => '订购中',
            default => '未知状态',
        };
    }

    public function getColorClass(): string
    {
        return match ($this->value) {
            self::IN_STOCK => 'text-green-600',
            self::LOW_STOCK => 'text-yellow-600',
            self::OUT_OF_STOCK => 'text-red-600',
            self::DISCONTINUED => 'text-gray-500',
            self::ON_ORDER => 'text-blue-600',
            default => 'text-gray-400',
        };
    }

    public function getBadgeClass(): string
    {
        return match ($this->value) {
            self::IN_STOCK => 'bg-green-100 text-green-800',
            self::LOW_STOCK => 'bg-yellow-100 text-yellow-800',
            self::OUT_OF_STOCK => 'bg-red-100 text-red-800',
            self::DISCONTINUED => 'bg-gray-100 text-gray-800',
            self::ON_ORDER => 'bg-blue-100 text-blue-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getPriority(): int
    {
        return match ($this->value) {
            self::OUT_OF_STOCK => 1, // Highest priority
            self::LOW_STOCK => 2,
            self::ON_ORDER => 3,
            self::IN_STOCK => 4,
            self::DISCONTINUED => 5, // Lowest priority
            default => 6,
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
                'Invalid inventory status "%s". Valid statuses are: %s',
                $value,
                implode(', ', self::VALID_STATUSES)
            ));
        }
    }

    public static function determineFromQuantity(int $quantity, int $lowStockThreshold = 10): self
    {
        if ($quantity <= 0) {
            return self::outOfStock();
        }

        if ($quantity <= $lowStockThreshold) {
            return self::lowStock();
        }

        return self::inStock();
    }
}