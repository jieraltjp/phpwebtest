<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

use App\Domain\Abstractions\AbstractValueObject;

final class Email extends AbstractValueObject
{
    private string $value;

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = strtolower(trim($value));
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function getDomain(): string
    {
        return substr(strrchr($this->value, '@'), 1);
    }

    public function getLocalPart(): string
    {
        return strstr($this->value, '@', true);
    }

    protected function getValue(): mixed
    {
        return $this->value;
    }

    private function validate(string $value): void
    {
        $trimmed = trim($value);
        
        if (empty($trimmed)) {
            throw new \InvalidArgumentException('Email cannot be empty');
        }

        if (strlen($trimmed) > 255) {
            throw new \InvalidArgumentException('Email cannot exceed 255 characters');
        }

        if (!filter_var($trimmed, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }

        // Additional validation for B2B context
        if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $trimmed)) {
            throw new \InvalidArgumentException('Email format is not suitable for business use');
        }
    }
}