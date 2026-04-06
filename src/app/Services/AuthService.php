<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function registerStaff(array $attributes): User
    {
        return User::create([
            'name'     => $attributes['name'],
            'email'    => $attributes['email'],
            'password' => Hash::make($attributes['password']),
            'role'     => User::ROLE_STAFF,
        ]);
    }
}