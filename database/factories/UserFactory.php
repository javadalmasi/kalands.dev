<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Services\Auth\PasswordHashService;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    protected $model = User::class;

    public function definition(): array
    {
        $password = app(PasswordHashService::class)->make('password');

        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => fake()->randomElement([
                now(),
                null
            ]),
            'phone' => '09'.fake()->numberBetween(112223344, 667778899),
            'phone_verified_at' => fake()->randomElement([
                now(),
                null
            ]),
            'password_hash' => $password['hash'],
            'password_salt' => $password['salt'],
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }
}
