<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Like;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MylistTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_only_liked_items()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $likedItem = Item::factory()->create(['item_name' => 'Liked Item']);
        Like::create(['user_id' => $user->id, 'item_id' => $likedItem->id]);

        $unlikedItem = Item::factory()->create(['item_name' => 'Unliked Item', 'user_id' => User::factory()->create()->id]);

        $ownItem = Item::factory()->create(['item_name' => 'My Own Item', 'user_id' => $user->id]);


        $response = $this->getJson('/api/mylist');

        $response->assertStatus(200)
            ->assertJsonFragment(['item_name' => 'Liked Item'])
            ->assertJsonMissing(['item_name' => 'Unliked Item'])
            ->assertJsonMissing(['item_name' => 'My Own Item']);
    }

    public function test_sold_items()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 未購入の「いいね」商品
        $availableLikedItem = Item::factory()->create(['item_name' => 'Available Liked Item', 'sold_at' => null]);
        Like::create(['user_id' => $user->id, 'item_id' => $availableLikedItem->id]);

        // 購入済みの「いいね」商品
        $otherSeller = User::factory()->create();
        $soldLikedItem = Item::factory()->sold()->create([
            'item_name' => 'Sold Liked Item',
            'user_id' => $otherSeller->id,
        ]);
        Like::create(['user_id' => $user->id, 'item_id' => $soldLikedItem->id]);

        $response = $this->getJson('/api/mylist');

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'item_name' => 'Available Liked Item',
            'sold_at' => null,
        ]);

        $response->assertJsonFragment([
            'item_name' => 'Sold Liked Item',
            'sold_at' => $soldLikedItem->sold_at->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_own_items_are_not_displayed_on_mylist()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $ownItem = Item::factory()->create(['item_name' => 'My Own Item', 'user_id' => $user->id]);
        Like::create(['user_id' => $user->id, 'item_id' => $ownItem->id]);

        $otherUserLikedItem = Item::factory()->create(['item_name' => 'Other User Liked Item', 'user_id' => User::factory()->create()->id]);
        Like::create(['user_id' => $user->id, 'item_id' => $otherUserLikedItem->id]);

        $response = $this->getJson('/api/mylist');

        $response->assertStatus(200)
            ->assertJsonMissing(['item_name' => 'My Own Item'])
            ->assertJsonFragment(['item_name' => 'Other User Liked Item']);
    }


    public function test_unauthenticated_user_cannot_access_mylist()
    {
        $response = $this->getJson('/api/mylist');

        $response->assertStatus(401);
        $response->assertJson(['message' => 'Unauthenticated.']);
    }
}