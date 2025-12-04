<?php

declare(strict_types=1);

namespace App\Domain\Order\ValueObjects;

use App\Domain\Abstractions\AbstractValueObject;

final class OrderId extends AbstractValueObject
{
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

    public static function generate(): self
    {
        // Generate B2B order number format: B2B-YYYYMMDD-XXXXX
        $date = date('Ymd');
        $sequence = str_pad((string) mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        return new self("B2B-{$date}-{$sequence}");
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function getDate(): string
    {
        if (preg_match('/B2B-(\d{8})-\d{5}/', $this->value, $matches)) {
            return $matches[1];
        }
        return '';
    }

    public function getSequence(): string
    {
        if (preg_match('/B2B-\d{8}-(\d{5})/', $this->value, $matches)) {
            return $matches[1];
        }
        return '';
    }

    public function isB2BOrder(): bool
    {
        return str_starts_with($this->value, 'B2B-');
    }

    public function getFormattedDate(): string
    {
        $date = $this->getDate();
        if (empty($date) || strlen($date) !== 8) {
            return '';
        }

        $year = substr($date, 0, 4);
        $month = substr($date, 4, 2);
        $day = substr($date, 6, 2);

        return "{$year}-{$month}-{$day}";
    }

    protected function getValue(): mixed
    {
        return $this->value;
    }

    private function validate(string $value): void
    {
        $trimmed = trim($value);
        
        if (empty($trimmed)) {
            throw new \InvalidArgumentException('Order ID cannot be empty');
        }

        if (strlen($trimmed) > 50) {
            throw new \InvalidArgumentException('Order ID cannot exceed 50 characters');
        }

        // Allow alphanumeric, hyphens, and underscores
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $trimmed)) {
            throw new \InvalidArgumentException('Order ID can only contain letters, numbers, hyphens, and underscores');
        }
    }
}