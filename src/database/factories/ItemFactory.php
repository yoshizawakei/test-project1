<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Item;
use App\Models\User;

class ItemFactory extends Factory
{

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Item::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "item_name" => $this->faker->sentence(3),
            "price" => $this->faker->numberBetween(100, 100000),
            "description" => $this->faker->paragraph(),
            "image_path" => $this->faker->imageUrl(),
            "condition" => $this->faker->randomElement(['良好', '目立った傷や汚れなし', 'やや傷や汚れあり', '状態が悪い']),
            "user_id" => User::factory(),
            "brand_id" => $this->faker->numberBetween(1, 10),
            "sold_at" => null,
            "buyer_id" => null,
            "created_at" => now(),
            "updated_at" => now(),
        ];
    }

    public function sold()
    {
        return $this->state(function (array $attributes) {
            return [
                'sold_at' => now(),
                'buyer_id' => User::factory(),
            ];
        });
    }
}
