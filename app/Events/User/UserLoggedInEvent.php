<?php

namespace App\Events\User;

use App\Events\AbstractEvent;
use App\Models\User;

class UserLoggedInEvent extends AbstractEvent
{
    public function __construct(User $user, array $metadata = [])
    {
        $data = [
            'user_id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'login_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'login_time' => now()->toISOString(),
        ];

        $defaultMetadata = [
            'source' => 'web_login',
            'category' => 'user_activity',
            'importance' => 'medium'
        ];

        parent::__construct($data, array_merge($defaultMetadata, $metadata), false, 5);
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

    public function getLoginIp(): string
    {
        return $this->getDataField('login_ip');
    }

    public function getUserAgent(): string
    {
        return $this->getDataField('user_agent');
    }

    public function getLoginTime(): string
    {
        return $this->getDataField('login_time');
    }
}