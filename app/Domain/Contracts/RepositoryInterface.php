<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

interface RepositoryInterface
{
    /**
     * Find an entity by its identifier.
     */
    public function findById(mixed $id): ?EntityInterface;

    /**
     * Save an entity to the repository.
     */
    public function save(EntityInterface $entity): void;

    /**
     * Delete an entity from the repository.
     */
    public function delete(EntityInterface $entity): void;

    /**
     * Get all entities.
     *
     * @return array<EntityInterface>
     */
    public function findAll(): array;
}