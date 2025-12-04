<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

interface EntityInterface
{
    /**
     * Get the entity identifier.
     */
    public function getId(): mixed;

    /**
     * Check if two entities are the same.
     */
    public function equals(EntityInterface $other): bool;
}