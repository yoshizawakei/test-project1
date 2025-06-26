<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Comment;

class CommentFeatureTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function test_logged_in_user_can_post_comment()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $commentData = ["comment" => "これはテストコメントです。"];

        $response = $this->actingAs($user)->post(route("comments.store", $item), $commentData);

        $response->assertStatus(302);
        $response->assertRedirect(route("items.detail", $item));

        $this->assertDatabaseHas("comments", [
            "user_id" => $user->id,
            "item_id" => $item->id,
            "comment" => $commentData["comment"],
        ]);

        $item->refresh();
        $this->assertEquals(1, $item->comments->count());
    }

    public function test_guest_user_cannot_post_comment()
    {
        $item = Item::factory()->create();

        $commentData = ["comment" => "これはテストコメントです。"];

        $response = $this->post(route("comments.store", $item), $commentData);

        $response->assertStatus(302);
        $response->assertRedirect(route("login"));

        $this->assertDatabaseMissing("comments", [
            "item_id" => $item->id,
            "comment" => $commentData["comment"],
        ]);

        $item->refresh();
        $this->assertEquals(0, $item->comments->count());
    }

    public function test_comment_field_is_required()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $commentData = ["comment" => ""];

        $response = $this->actingAs($user)->post(route("comments.store", $item), $commentData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors("comment");

        $this->assertDatabaseMissing("comments", [
            "user_id" => $user->id,
            "item_id" => $item->id,
            "comment" => $commentData["comment"],
        ]);
    }

    public function test_comment_field_max_length()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $longComment = str_repeat("a", 256); // 256文字のコメント

        $response = $this->actingAs($user)->post(route("comments.store", $item), ["comment" => $longComment]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors("comment");

        $this->assertDatabaseMissing("comments", [
            "user_id" => $user->id,
            "item_id" => $item->id,
            "comment" => $longComment,
        ]);
    }
}
