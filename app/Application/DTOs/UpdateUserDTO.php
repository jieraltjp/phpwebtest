<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final class UpdateUserDTO
{
    public function __construct(
        public readonly ?string $email = null,
        public readonly ?string $username = null,
        public readonly ?string $passwordHash = null,
        public readonly ?string $role = null,
        public readonly ?string $status = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['email'] ?? null,
            $data['username'] ?? null,
            $data['password_hash'] ?? null,
            $data['role'] ?? null,
            $data['status'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'email' => $this->email,
            'username' => $this->username,
            'password_hash' => $this->passwordHash,
            'role' => $this->role,
            'status' => $this->status,
        ], fn($value) => $value !== null);
    }

    public function hasChanges(): bool
    {
        return $this->email !== null ||
               $this->username !== null ||
               $this->passwordHash !== null ||
               $this->role !== null ||
               $this->status !== null;
    }

    public function validate(): array
    {
        $errors = [];

        if ($this->email !== null && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }

        if ($this->username !== null && empty(trim($this->username))) {
            $errors[] = 'Username cannot be empty';
        }

        if ($this->role !== null) {
            $validRoles = ['admin', 'purchase_manager', 'sales_representative', 'customer', 'supplier'];
            if (!in_array($this->role, $validRoles, true)) {
                $errors[] = 'Invalid role';
            }
        }

        if ($this->status !== null) {
            $validStatuses = ['active', 'inactive', 'suspended', 'pending_verification'];
            if (!in_array($this->status, $validStatuses, true)) {
                $errors[] = 'Invalid status';
            }
        }

        return $errors;
    }
}