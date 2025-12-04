<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\User\User;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\Username;
use App\Domain\User\ValueObjects\UserRole;
use App\Domain\User\ValueObjects\UserStatus;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\Services\UserDomainService;
use App\Application\DTOs\CreateUserDTO;
use App\Application\DTOs\UpdateUserDTO;
use App\Application\DTOs\UserDTO;

final class UserApplicationService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserDomainService $userDomainService,
        private EventDispatcherService $eventDispatcher
    ) {
    }

    public function createUser(CreateUserDTO $dto): UserDTO
    {
        // Validate using domain service
        $validationErrors = $this->userDomainService->validateUserRegistration(
            Username::fromString($dto->username),
            Email::fromString($dto->email),
            UserRole::fromString($dto->role)
        );

        if (!empty($validationErrors)) {
            throw new \InvalidArgumentException(implode(', ', $validationErrors));
        }

        // Generate new user ID
        $userId = UserId::fromInt($this->generateUserId());

        // Create user entity
        $user = User::register(
            $userId,
            Username::fromString($dto->username),
            Email::fromString($dto->email),
            $dto->passwordHash,
            UserRole::fromString($dto->role)
        );

        // Save user
        $this->userRepository->save($user);

        // Dispatch domain events
        $this->eventDispatcher->dispatch($user->getDomainEvents());
        $user->clearDomainEvents();

        return $this->mapToDTO($user);
    }

    public function getUserById(int $userId): ?UserDTO
    {
        $user = $this->userRepository->findById(UserId::fromInt($userId));
        
        if (!$user) {
            return null;
        }

        return $this->mapToDTO($user);
    }

    public function getUserByEmail(string $email): ?UserDTO
    {
        $user = $this->userRepository->findByEmail(Email::fromString($email));
        
        if (!$user) {
            return null;
        }

        return $this->mapToDTO($user);
    }

    public function getUserByUsername(string $username): ?UserDTO
    {
        $user = $this->userRepository->findByUsername(Username::fromString($username));
        
        if (!$user) {
            return null;
        }

        return $this->mapToDTO($user);
    }

    public function updateUser(int $userId, UpdateUserDTO $dto): UserDTO
    {
        $user = $this->userRepository->findById(UserId::fromInt($userId));
        
        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        // Update fields if provided
        if ($dto->email !== null) {
            $user->updateEmail(Email::fromString($dto->email));
        }

        if ($dto->username !== null) {
            $user->updateUsername(Username::fromString($dto->username));
        }

        if ($dto->passwordHash !== null) {
            $user->updatePassword($dto->passwordHash);
        }

        if ($dto->role !== null) {
            $user->changeRole(UserRole::fromString($dto->role));
        }

        if ($dto->status !== null) {
            $user->changeStatus(UserStatus::fromString($dto->status));
        }

        // Save changes
        $this->userRepository->save($user);

        // Dispatch domain events
        $this->eventDispatcher->dispatch($user->getDomainEvents());
        $user->clearDomainEvents();

        return $this->mapToDTO($user);
    }

    public function changeUserRole(int $userId, string $newRole, ?string $changedBy = null): UserDTO
    {
        $user = $this->userRepository->findById(UserId::fromInt($userId));
        
        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        $role = UserRole::fromString($newRole);

        if (!$this->userDomainService->canUserChangeRole($user, $role)) {
            throw new \InvalidArgumentException('User cannot change to this role');
        }

        $user->changeRole($role, $changedBy);
        $this->userRepository->save($user);

        // Dispatch domain events
        $this->eventDispatcher->dispatch($user->getDomainEvents());
        $user->clearDomainEvents();

        return $this->mapToDTO($user);
    }

    public function changeUserStatus(int $userId, string $newStatus, ?string $reason = null): UserDTO
    {
        $user = $this->userRepository->findById(UserId::fromInt($userId));
        
        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        $status = UserStatus::fromString($newStatus);

        if (!$this->userDomainService->canUserChangeStatus($user, $status)) {
            throw new \InvalidArgumentException('User cannot change to this status');
        }

        $user->changeStatus($status, $reason);
        $this->userRepository->save($user);

        // Dispatch domain events
        $this->eventDispatcher->dispatch($user->getDomainEvents());
        $user->clearDomainEvents();

        return $this->mapToDTO($user);
    }

    public function recordUserLogin(int $userId): void
    {
        $user = $this->userRepository->findById(UserId::fromInt($userId));
        
        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        $user->recordLogin();
        $this->userRepository->save($user);
    }

    public function getUsersByRole(string $role): array
    {
        $users = $this->userRepository->findByRole(UserRole::fromString($role));
        
        return array_map([$this, 'mapToDTO'], $users);
    }

    public function getUsersByStatus(string $status): array
    {
        $users = $this->userRepository->findByStatus(UserStatus::fromString($status));
        
        return array_map([$this, 'mapToDTO'], $users);
    }

    public function getActiveUsers(): array
    {
        $users = $this->userRepository->findActiveUsers();
        
        return array_map([$this, 'mapToDTO'], $users);
    }

    public function searchUsers(string $keyword): array
    {
        $users = $this->userRepository->searchByKeyword($keyword);
        
        return array_map([$this, 'mapToDTO'], $users);
    }

    public function isEmailAvailable(string $email): bool
    {
        return $this->userDomainService->isEmailUnique(Email::fromString($email));
    }

    public function isUsernameAvailable(string $username): bool
    {
        return $this->userDomainService->isUsernameUnique(Username::fromString($username));
    }

    public function getUserRiskScore(int $userId): int
    {
        $user = $this->userRepository->findById(UserId::fromInt($userId));
        
        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        return $this->userDomainService->calculateUserRiskScore($user);
    }

    public function canUserPerformAction(int $userId, string $action): bool
    {
        $user = $this->userRepository->findById(UserId::fromInt($userId));
        
        if (!$user) {
            return false;
        }

        return $this->userDomainService->isUserAllowedToPerformAction($user, $action);
    }

    private function mapToDTO(User $user): UserDTO
    {
        return new UserDTO(
            $user->getId()->toInt(),
            $user->getUsername()->toString(),
            $user->getEmail()->toString(),
            $user->getRole()->getValue(),
            $user->getStatus()->getValue(),
            $user->getCreatedAt(),
            $user->getUpdatedAt(),
            $user->getLastLoginAt(),
            $user->getRole()->getDisplayName(),
            $user->getStatus()->getDisplayName(),
            $user->getStatus()->getColorClass()
        );
    }

    private function generateUserId(): int
    {
        // In a real application, you might use a more sophisticated ID generation strategy
        return (int) (microtime(true) * 1000);
    }
}