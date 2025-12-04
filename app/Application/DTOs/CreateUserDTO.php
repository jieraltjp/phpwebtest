<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final class CreateUserDTO
{
    public function __construct(
        public readonly string $username,
        public readonly string $email,
        public readonly string $passwordHash,
        public readonly string $role = 'customer'
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['username'],
            $data['email'],
            $data['password_hash'],
            $data['role'] ?? 'customer'
        );
    }

    public function toArray(): array
    {
        return [
            'username' => $this->username,
            'email' => $this->email,
            'password_hash' => $this->passwordHash,
            'role' => $this->role,
        ];
    }

    public function validate(): array
    {
        $errors = [];

        if (empty(trim($this->username))) {
            $errors[] = 'Username is required';
        }

        if (empty(trim($this->email))) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }

        if (empty($this->passwordHash)) {
            $errors[] = 'Password hash is required';
        }

        $validRoles = ['admin', 'purchase_manager', 'sales_representative', 'customer', 'supplier'];
        if (!in_array($this->role, $validRoles, true)) {
            $errors[] = 'Invalid role';
        }

        return $errors;
    }
}