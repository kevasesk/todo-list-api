<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    public function createUser(array $data): User
    {
        $userData = [
            'name' => $data['name'],
            'email' => $data['name'],
            'password' => Hash::make($data['password']),
        ];
        return User::create($userData);
    }

    public function findUserByName(string $name): ?User
    {
        return User::where('name', $name)->first();
    }
}
