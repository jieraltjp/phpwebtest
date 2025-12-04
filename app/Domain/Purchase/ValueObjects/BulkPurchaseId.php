<?php

declare(strict_types=1);

namespace App\Domain\Purchase\ValueObjects;

use App\Domain\Abstractions\AbstractValueObject;

final class BulkPurchaseId extends AbstractValueObject
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
        // Generate bulk purchase ID format: BULK-YYYYMMDD-XXXXX
        $date = date('Ymd');
        $sequence = str_pad((string) mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        return new self("BULK-{$date}-{$sequence}");
    }

    public function toString(): string
    {
        return $this->value;
    }

    protected function getValue(): mixed
    {
        return $this->value;
    }

    private function validate(string $value): void
    {
        $trimmed = trim($value);
        
        if (empty($trimmed)) {
            throw new \InvalidArgumentException('Bulk Purchase ID cannot be empty');
        }

        if (strlen($trimmed) > 50) {
            throw new \InvalidArgumentException('Bulk Purchase ID cannot exceed 50 characters');
        }

        if (!preg_match('/^[A-Za-z0-9_-]+$/', $trimmed)) {
            throw new \InvalidArgumentException('Bulk Purchase ID can only contain letters, numbers, hyphens, and underscores');
        }
    }
}