<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Services\Auth\PasswordHashService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Admin>
 */
class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition(): array
    {
        $password = app(PasswordHashService::class)->make('password');

        return [
            'full_name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email_address' => fake()->unique()->safeEmail(),
            'mobile_number' => '09'.fake()->numberBetween(112223344, 667778899),
            'password_hash' => $password['hash'],
            'password_salt' => $password['salt'],
            'is_active' => true,
        ];
    }
}
