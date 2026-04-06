<?php

namespace Database\Factories;

use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    public function definition()
    {
        $faker = FakerFactory::create('ja_JP');

        return [
            'name' => $faker->name(),
            'email' => $faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' =>  Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => User::ROLE_USER
        ];
    }

    public function unverified()
    {
        return $this->state(fn () => [
                'email_verified_at' => null,
            ]);
    }

    public function admin()
    {
        return $this->state(fn() => ['role' => User::ROLE_ADMIN]);
    }

    public function staff()
    {
        return $this->state(fn() => ['role' => User::ROLE_STAFF]);
    }
}
