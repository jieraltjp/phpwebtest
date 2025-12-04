<?php

declare(strict_types=1);

namespace App\Domain\Abstractions;

use App\Domain\Contracts\EntityInterface;

abstract class AbstractEntity implements EntityInterface
{
    protected mixed $id;

    protected function __construct(mixed $id)
    {
        $this->id = $id;
    }

    public function getId(): mixed
    {
        return $this->id;
    }

    public function equals(EntityInterface $other): bool
    {
        return static::class === get_class($other) && $this->getId() === $other->getId();
    }
}