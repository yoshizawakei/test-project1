<?php

namespace Database\Factories; // ★この名前空間が正しいことを確認

use App\Models\Like; // Likeモデルをuse
use App\Models\User; // Userモデルをuse
use App\Models\Item; // Itemモデルをuse
use Illuminate\Database\Eloquent\Factories\Factory;

class LikeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Like::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'item_id' => Item::factory(),
        ];
    }
}
