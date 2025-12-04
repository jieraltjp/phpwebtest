<?php

declare(strict_types=1);

namespace App\Domain\User\Services;

use App\Domain\User\User;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\Username;
use App\Domain\User\ValueObjects\UserRole;
use App\Domain\User\ValueObjects\UserStatus;
use App\Domain\Contracts\DomainServiceInterface;
use App\Domain\Contracts\RepositoryInterface;

final class UserDomainService implements DomainServiceInterface
{
    public function __construct(
        private RepositoryInterface $userRepository
    ) {
    }

    public function getServiceName(): string
    {
        return 'UserDomainService';
    }

    public function isEmailUnique(Email $email): bool
    {
        $existingUsers = $this->userRepository->findAll();
        
        foreach ($existingUsers as $user) {
            if ($user instanceof User && $user->getEmail()->equals($email)) {
                return false;
            }
        }

        return true;
    }

    public function isUsernameUnique(Username $username): bool
    {
        $existingUsers = $this->userRepository->findAll();
        
        foreach ($existingUsers as $user) {
            if ($user instanceof User && $user->getUsername()->equals($username)) {
                return false;
            }
        }

        return true;
    }

    public function canUserChangeRole(User $user, UserRole $newRole): bool
    {
        // Business rules for role changes
        if ($user->isAdmin()) {
            // Admins can change to any role
            return true;
        }

        if ($user->isSuspended()) {
            // Suspended users cannot change roles
            return false;
        }

        if ($newRole->isAdmin() && !$user->isAdmin()) {
            // Only existing admins can grant admin role
            return false;
        }

        return true;
    }

    public function canUserChangeStatus(User $user, UserStatus $newStatus): bool
    {
        // Business rules for status changes
        if ($newStatus->isActive() && $user->isSuspended()) {
            // Suspended users need manual review to become active
            return false;
        }

        if ($newStatus->isSuspended() && $user->isAdmin()) {
            // Admins cannot be suspended (business rule)
            return false;
        }

        return true;
    }

    public function validateUserRegistration(
        Username $username,
        Email $email,
        UserRole $role
    ): array {
        $errors = [];

        if (!$this->isUsernameUnique($username)) {
            $errors[] = 'Username is already taken';
        }

        if (!$this->isEmailUnique($email)) {
            $errors[] = 'Email is already registered';
        }

        // Additional business validation
        if ($role->isAdmin()) {
            $errors[] = 'Admin role cannot be assigned during registration';
        }

        return $errors;
    }

    public function getDefaultRoleForEmail(Email $email): UserRole
    {
        $domain = $email->getDomain();
        
        // Business logic: assign supplier role for certain domains
        $supplierDomains = ['supplier.com', 'vendor.co.jp', 'manufacturer.cn'];
        
        if (in_array($domain, $supplierDomains, true)) {
            return UserRole::supplier();
        }

        // Default to customer role
        return UserRole::customer();
    }

    public function shouldRequireEmailVerification(Email $email): bool
    {
        $domain = $email->getDomain();
        
        // Business domains that don't require verification
        $trustedDomains = ['manpou.jp', 'banho.co.jp'];
        
        return !in_array($domain, $trustedDomains, true);
    }

    public function calculateUserRiskScore(User $user): int
    {
        $score = 0;

        // Base score by role
        if ($user->isAdmin()) {
            $score += 50;
        } elseif ($user->getRole()->isPurchaseManager()) {
            $score += 30;
        } elseif ($user->getRole()->isSalesRepresentative()) {
            $score += 20;
        } else {
            $score += 10;
        }

        // Status impact
        if ($user->isSuspended()) {
            $score += 40;
        } elseif ($user->isPendingVerification()) {
            $score += 20;
        }

        // Account age
        $daysSinceCreation = (new \DateTimeImmutable())->diff($user->getCreatedAt())->days;
        if ($daysSinceCreation < 7) {
            $score += 15;
        } elseif ($daysSinceCreation < 30) {
            $score += 10;
        }

        return min($score, 100);
    }

    public function isUserAllowedToPerformAction(User $user, string $action): bool
    {
        return match ($action) {
            'view_reports' => $user->canViewReports(),
            'manage_products' => $user->canManageProducts(),
            'manage_orders' => $user->canManageOrders(),
            'place_orders' => $user->canPlaceOrders(),
            'manage_users' => $user->isAdmin(),
            'view_analytics' => $user->canViewReports(),
            'bulk_purchase' => $user->canPlaceOrders() && $user->getRole()->isPurchaseManager(),
            default => false,
        };
    }
}