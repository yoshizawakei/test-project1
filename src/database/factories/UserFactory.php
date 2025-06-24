<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "name" => $this->faker->name(),
            "email" => $this->faker->unique()->safeEmail(),
            "email_verified_at" => now(),
            "password" => bcrypt('password'),
            "profile_configured" => $this->faker->boolean(), // 50% chance of being true
            "remember_token" => Str::random(10),
            "created_at" => now(),
            "updated_at" => now(),
        ];
    }
}
