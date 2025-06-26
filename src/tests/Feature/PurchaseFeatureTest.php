<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Profile;

class PurchaseFeatureTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_user_purchase_item()
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $item = Item::factory()->create(["user_id" => $seller->id, "sold_at" => null, "buyer_id" => null]);

        Profile::factory()->create(["user_id" => $buyer->id]);

        $response = $this->actingAs($buyer)->post(route("items.completePurchase", $item), [
            "payment_method" => "credit_card",
            "user_profile_exists" => true,
        ]);

        $response->assertStatus(200);
        $response->assertViewIs("items.thanks");

        $item->refresh();
        $this->assertNotNull($item->sold_at);
        $this->assertEquals($buyer->id, $item->buyer_id);
    }

    public function purchased_item_is_displayed_as_sold_on_item_list()
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $seller->id, 'sold_at' => null, 'buyer_id' => null]);

        Profile::factory()->create(['user_id' => $buyer->id]);

        $this->actingAs($buyer)->post(route('items.completePurchase', $item));

        $response = $this->get(route('top.index'));
        $response->assertStatus(200);

        $response->assertSee('<div class="sold-out-overlay">SOLD</div>', false);
    }

    public function purchased_item_is_added_to_purchased_items_list_on_profile()
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $seller->id, 'sold_at' => null, 'buyer_id' => null]);

        Profile::factory()->create(['user_id' => $buyer->id]);

        $this->actingAs($buyer)->post(route('items.completePurchase', $item));

        $response = $this->actingAs($buyer)->get(route('mypage.purchased_items'));


        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $item->id,
            'item_name' => $item->item_name,
        ]);
    }


}
