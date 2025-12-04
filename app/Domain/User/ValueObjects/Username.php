<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

use App\Domain\Abstractions\AbstractValueObject;

final class Username extends AbstractValueObject
{
    private string $value;

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = trim($value);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
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
            throw new \InvalidArgumentException('Username cannot be empty');
        }

        if (strlen($trimmed) < 3) {
            throw new \InvalidArgumentException('Username must be at least 3 characters long');
        }

        if (strlen($trimmed) > 50) {
            throw new \InvalidArgumentException('Username cannot exceed 50 characters');
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $trimmed)) {
            throw new \InvalidArgumentException('Username can only contain letters, numbers, underscores, and hyphens');
        }

        // Business rule: username cannot start or end with underscore or hyphen
        if (str_starts_with($trimmed, '_') || str_starts_with($trimmed, '-') ||
            str_ends_with($trimmed, '_') || str_ends_with($trimmed, '-')) {
            throw new \InvalidArgumentException('Username cannot start or end with underscore or hyphen');
        }
    }
}