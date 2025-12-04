<?php

namespace App\Events\User;

use App\Events\AbstractEvent;
use App\Models\User;

class UserRegisteredEvent extends AbstractEvent
{
    public function __construct(User $user, array $metadata = [])
    {
        $data = [
            'user_id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'name' => $user->name ?? null,
            'registration_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        $defaultMetadata = [
            'source' => 'web_registration',
            'category' => 'user_lifecycle',
            'importance' => 'high'
        ];

        parent::__construct($data, array_merge($defaultMetadata, $metadata), true, 10);
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

    

    public function getRegistrationIp(): string
    {
        return $this->getDataField('registration_ip');
    }

    public function getUserAgent(): string
    {
        return $this->getDataField('user_agent');
    }
}