<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

interface ValueObjectInterface
{
    /**
     * Check if two value objects are equal.
     */
    public function equals(ValueObjectInterface $other): bool;

    /**
     * Get the value object as string.
     */
    public function toString(): string;

    /**
     * Get the value object as string (magic method).
     */
    public function __toString(): string;
}