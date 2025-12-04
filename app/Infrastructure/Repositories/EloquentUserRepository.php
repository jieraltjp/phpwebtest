<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\User\User;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\Username;
use App\Domain\User\ValueObjects\UserRole;
use App\Domain\User\ValueObjects\UserStatus;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\Contracts\EntityInterface;
use App\Models\User as UserModel;

final class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(UserId $id): ?User
    {
        $userModel = UserModel::find($id->toInt());
        
        if (!$userModel) {
            return null;
        }

        return $this->mapToDomainEntity($userModel);
    }

    public function save(User $user): void
    {
        $userModel = UserModel::find($user->getId()->toInt());
        
        if (!$userModel) {
            $userModel = new UserModel();
            $userModel->id = $user->getId()->toInt();
        }

        $userModel->username = $user->getUsername()->toString();
        $userModel->email = $user->getEmail()->toString();
        $userModel->password = $user->getPasswordHash();
        $userModel->role = $user->getRole()->getValue();
        $userModel->status = $user->getStatus()->getValue();
        $userModel->created_at = $user->getCreatedAt();
        $userModel->updated_at = $user->getUpdatedAt();
        $userModel->last_login_at = $user->getLastLoginAt();
        $userModel->remember_token = $user->getRememberToken();
        
        $userModel->save();
    }

    public function delete(User $user): void
    {
        UserModel::destroy($user->getId()->toInt());
    }

    public function findAll(): array
    {
        $userModels = UserModel::all();
        
        return $userModels->map(function ($userModel) {
            return $this->mapToDomainEntity($userModel);
        })->toArray();
    }

    public function findByEmail(Email $email): ?User
    {
        $userModel = UserModel::where('email', $email->toString())->first();
        
        if (!$userModel) {
            return null;
        }

        return $this->mapToDomainEntity($userModel);
    }

    public function findByUsername(Username $username): ?User
    {
        $userModel = UserModel::where('username', $username->toString())->first();
        
        if (!$userModel) {
            return null;
        }

        return $this->mapToDomainEntity($userModel);
    }

    public function findByRole(UserRole $role): array
    {
        $userModels = UserModel::where('role', $role->getValue())->get();
        
        return $userModels->map(function ($userModel) {
            return $this->mapToDomainEntity($userModel);
        })->toArray();
    }

    public function findByStatus(UserStatus $status): array
    {
        $userModels = UserModel::where('status', $status->getValue())->get();
        
        return $userModels->map(function ($userModel) {
            return $this->mapToDomainEntity($userModel);
        })->toArray();
    }

    public function findByRoleAndStatus(UserRole $role, UserStatus $status): array
    {
        $userModels = UserModel::where('role', $role->getValue())
                              ->where('status', $status->getValue())
                              ->get();
        
        return $userModels->map(function ($userModel) {
            return $this->mapToDomainEntity($userModel);
        })->toArray();
    }

    public function findActiveUsers(): array
    {
        return $this->findByStatus(UserStatus::active());
    }

    public function findPendingVerification(): array
    {
        return $this->findByStatus(UserStatus::pendingVerification());
    }

    public function findSuspendedUsers(): array
    {
        return $this->findByStatus(UserStatus::suspended());
    }

    public function findUsersWhoCanManageOrders(): array
    {
        $roles = ['admin', 'purchase_manager', 'sales_representative'];
        $userModels = UserModel::whereIn('role', $roles)
                              ->where('status', 'active')
                              ->get();
        
        return $userModels->map(function ($userModel) {
            return $this->mapToDomainEntity($userModel);
        })->toArray();
    }

    public function findUsersWhoCanManageProducts(): array
    {
        $roles = ['admin', 'purchase_manager'];
        $userModels = UserModel::whereIn('role', $roles)
                              ->where('status', 'active')
                              ->get();
        
        return $userModels->map(function ($userModel) {
            return $this->mapToDomainEntity($userModel);
        })->toArray();
    }

    public function findCustomers(): array
    {
        return $this->findByRole(UserRole::customer());
    }

    public function findSuppliers(): array
    {
        return $this->findByRole(UserRole::supplier());
    }

    public function findAdmins(): array
    {
        return $this->findByRole(UserRole::admin());
    }

    public function countByRole(UserRole $role): int
    {
        return UserModel::where('role', $role->getValue())->count();
    }

    public function countByStatus(UserStatus $status): int
    {
        return UserModel::where('status', $status->getValue())->count();
    }

    public function countTotalUsers(): int
    {
        return UserModel::count();
    }

    public function existsByEmail(Email $email): bool
    {
        return UserModel::where('email', $email->toString())->exists();
    }

    public function existsByUsername(Username $username): bool
    {
        return UserModel::where('username', $username->toString())->exists();
    }

    public function findRecentlyRegistered(int $days = 7): array
    {
        $cutoffDate = now()->subDays($days);
        $userModels = UserModel::where('created_at', '>=', $cutoffDate)->get();
        
        return $userModels->map(function ($userModel) {
            return $this->mapToDomainEntity($userModel);
        })->toArray();
    }

    public function findInactiveUsers(int $days = 30): array
    {
        $cutoffDate = now()->subDays($days);
        $userModels = UserModel::where('last_login_at', '<', $cutoffDate)
                              ->orWhereNull('last_login_at')
                              ->get();
        
        return $userModels->map(function ($userModel) {
            return $this->mapToDomainEntity($userModel);
        })->toArray();
    }

    public function searchByKeyword(string $keyword): array
    {
        $userModels = UserModel::where('username', 'like', "%{$keyword}%")
                              ->orWhere('email', 'like', "%{$keyword}%")
                              ->get();
        
        return $userModels->map(function ($userModel) {
            return $this->mapToDomainEntity($userModel);
        })->toArray();
    }

    private function mapToDomainEntity(UserModel $userModel): User
    {
        return User::createExisting(
            UserId::fromInt($userModel->id),
            Username::fromString($userModel->username),
            Email::fromString($userModel->email),
            $userModel->password,
            UserRole::fromString($userModel->role),
            UserStatus::fromString($userModel->status),
            $userModel->created_at,
            $userModel->updated_at,
            $userModel->last_login_at,
            $userModel->remember_token
        );
    }
}