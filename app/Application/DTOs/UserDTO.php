<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final class UserDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $username,
        public readonly string $email,
        public readonly string $role,
        public readonly string $status,
        public readonly \DateTimeImmutable $createdAt,
        public readonly ?\DateTimeImmutable $updatedAt,
        public readonly ?\DateTimeImmutable $lastLoginAt,
        public readonly string $roleDisplayName,
        public readonly string $statusDisplayName,
        public readonly string $statusColorClass
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            'status' => $this->status,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'last_login_at' => $this->lastLoginAt?->format('Y-m-d H:i:s'),
            'role_display_name' => $this->roleDisplayName,
            'status_display_name' => $this->statusDisplayName,
            'status_color_class' => $this->statusColorClass,
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function isSupplier(): bool
    {
        return $this->role === 'supplier';
    }
}