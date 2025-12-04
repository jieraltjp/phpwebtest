<?php

declare(strict_types=1);

namespace App\Domain\Abstractions;

use App\Domain\Contracts\ValueObjectInterface;

abstract class AbstractValueObject implements ValueObjectInterface
{
    public function equals(ValueObjectInterface $other): bool
    {
        return static::class === get_class($other) && $this->getValue() === $other->getValue();
    }

    public function toString(): string
    {
        return (string) $this->getValue();
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Get the raw value of the value object.
     */
    abstract protected function getValue(): mixed;
}