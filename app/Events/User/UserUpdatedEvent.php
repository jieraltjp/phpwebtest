<?php

namespace App\Events\User;

use App\Events\AbstractEvent;
use App\Models\User;

class UserUpdatedEvent extends AbstractEvent
{
    public function __construct(User $user, array $originalData, array $updatedData, array $metadata = [])
    {
        $data = [
            'user_id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'original_data' => $originalData,
            'updated_data' => $updatedData,
            'changed_fields' => array_keys(array_diff_assoc($updatedData, $originalData)),
            'updated_by' => auth()->id() ?? 'system',
            'update_time' => now()->toISOString(),
        ];

        $defaultMetadata = [
            'source' => 'user_profile_update',
            'category' => 'user_lifecycle',
            'importance' => 'low'
        ];

        parent::__construct($data, array_merge($defaultMetadata, $metadata), false, 3);
    }

    public function getUserId(): int
    {
        return $this->getDataField('user_id');
    }

    public function getUsername(): string
    {
        return $this->getDataField('username');
    }

    public function getEmail(): string
    {
        return $this->getDataField('email');
    }

    public function getOriginalData(): array
    {
        return $this->getDataField('original_data');
    }

    public function getUpdatedData(): array
    {
        return $this->getDataField('updated_data');
    }

    public function getChangedFields(): array
    {
        return $this->getDataField('changed_fields');
    }

    public function getUpdatedBy(): string
    {
        return $this->getDataField('updated_by');
    }

    public function getUpdateTime(): string
    {
        return $this->getDataField('update_time');
    }

    public function hasFieldChanged(string $field): bool
    {
        return in_array($field, $this->getChangedFields());
    }
}