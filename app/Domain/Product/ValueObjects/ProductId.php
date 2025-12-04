<?php

declare(strict_types=1);

namespace App\Domain\Product\ValueObjects;

use App\Domain\Abstractions\AbstractValueObject;

final class ProductId extends AbstractValueObject
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
        // Generate SKU-like ID for B2B context
        $prefix = 'ALIBABA_SKU_';
        $randomPart = strtoupper(substr(md5(uniqid()), 0, 6));
        return new self($prefix . $randomPart);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function isAlibabaSku(): bool
    {
        return str_starts_with($this->value, 'ALIBABA_SKU_');
    }

    public function getPrefix(): string
    {
        return strstr($this->value, '_', true) ?: '';
    }

    public function getCode(): string
    {
        return substr(strrchr($this->value, '_'), 1);
    }

    protected function getValue(): mixed
    {
        return $this->value;
    }

    private function validate(string $value): void
    {
        $trimmed = trim($value);
        
        if (empty($trimmed)) {
            throw new \InvalidArgumentException('Product ID cannot be empty');
        }

        if (strlen($trimmed) > 100) {
            throw new \InvalidArgumentException('Product ID cannot exceed 100 characters');
        }

        // Allow alphanumeric, underscores, and hyphens
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $trimmed)) {
            throw new \InvalidArgumentException('Product ID can only contain letters, numbers, underscores, and hyphens');
        }
    }
}