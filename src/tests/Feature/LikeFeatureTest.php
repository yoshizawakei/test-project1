<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Like;

class LikeFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * テストのセットアップ
     * @test
     */
    public function a_user_can_like_a_product()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        // いいねの数が０であることを確認
        $this->assertEquals(0, $item->likesCount());

        $response = $this->actingAs($user)
            ->post(route('items.like.toggle', $item), [], ["X-Requested-With" => "XMLHttpRequest"]);

        $response->assertStatus(200);

        $response->assertJson([
            'liked' => true,
            'likes_count' => 1,
            'message' => 'いいねしました',
        ]);

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $item->refresh();
        $this->assertEquals(1, $item->likesCount());
    }

    public function a_user_can_unlike_a_product()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $item->likes()->create(['user_id' => $user->id]);

        $item->refresh();
        $this->assertEquals(1, $item->likesCount());

        $response = $this->actingAs($user)->post(route('items.like.toggle', $item),[],["X-Requested-With" => "XMLHttpRequest"]); 

        $response->assertStatus(200);
        $response->assertJson([
            'liked' => false,
            'likes_count' => 0,
            'message' => 'いいねを解除しました', 
        ]);

        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $item->refresh();
        $this->assertEquals(0, $item->likesCount());
    }

    public function liked_product_shows_correct_initial_state()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $item->likes()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->get(route('items.detail', $item));

        $response->assertStatus(200);

        $response->assertSee('<button id="like-button" class="btn btn-danger"', false);
        $response->assertSee('<i id="like-icon" class="fa-heart fas"', false);
        $response->assertSee('<span id="likes-count" class="like-count-display">' . $item->likesCount() . '</span>', false);
    }

    public function unauthenticated_user_is_redirected_to_login_when_liking()
    {
        $item = Item::factory()->create();

        $response = $this->post(route('items.like.toggle', $item));

        $response->assertRedirect(route('login'));
    }
}