<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\User\User;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\Username;
use App\Domain\User\ValueObjects\UserRole;
use App\Domain\User\ValueObjects\UserStatus;
use App\Domain\Contracts\RepositoryInterface;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function findById(UserId $id): ?User;

    public function save(User $user): void;

    public function delete(User $user): void;

    /**
     * @return User[]
     */
    public function findAll(): array;

    public function findByEmail(Email $email): ?User;

    public function findByUsername(Username $username): ?User;

    /**
     * @return User[]
     */
    public function findByRole(UserRole $role): array;

    /**
     * @return User[]
     */
    public function findByStatus(UserStatus $status): array;

    /**
     * @return User[]
     */
    public function findByRoleAndStatus(UserRole $role, UserStatus $status): array;

    /**
     * @return User[]
     */
    public function findActiveUsers(): array;

    /**
     * @return User[]
     */
    public function findPendingVerification(): array;

    /**
     * @return User[]
     */
    public function findSuspendedUsers(): array;

    /**
     * @return User[]
     */
    public function findUsersWhoCanManageOrders(): array;

    /**
     * @return User[]
     */
    public function findUsersWhoCanManageProducts(): array;

    /**
     * @return User[]
     */
    public function findCustomers(): array;

    /**
     * @return User[]
     */
    public function findSuppliers(): array;

    /**
     * @return User[]
     */
    public function findAdmins(): array;

    public function countByRole(UserRole $role): int;

    public function countByStatus(UserStatus $status): int;

    public function countTotalUsers(): int;

    public function existsByEmail(Email $email): bool;

    public function existsByUsername(Username $username): bool;

    /**
     * @return User[]
     */
    public function findRecentlyRegistered(int $days = 7): array;

    /**
     * @return User[]
     */
    public function findInactiveUsers(int $days = 30): array;

    /**
     * @return User[]
     */
    public function searchByKeyword(string $keyword): array;
}