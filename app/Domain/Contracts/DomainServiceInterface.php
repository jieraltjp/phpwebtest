<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

interface DomainServiceInterface
{
    /**
     * Get the domain service name.
     */
    public function getServiceName(): string;
}