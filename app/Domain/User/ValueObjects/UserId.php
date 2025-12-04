<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

use App\Domain\Abstractions\AbstractValueObject;

final class UserId extends AbstractValueObject
{
    private int $value;

    public function __construct(int $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public function toInt(): int
    {
        return $this->value;
    }

    protected function getValue(): mixed
    {
        return $this->value;
    }

    private function validate(int $value): void
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException('User ID must be a positive integer');
        }
    }
}