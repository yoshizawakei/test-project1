<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ItemSearchTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_item_search_partial_item_name()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Item::factory()->create(['item_name' => 'Test Item 1']);
        Item::factory()->create(['item_name' => 'Test Item 2']);
        Item::factory()->create(['item_name' => 'Another Item']);
        Item::factory()->create(['item_name' => 'Different Item']);

        $keyword = 'Test';

        $response = $this->get("/?search=" . $keyword);

        $response->assertStatus(200);

        $response->assertSeeText('Test Item 1');
        $response->assertSeeText('Test Item 2');
        $response->assertDontSeeText('Another Item');
    }

    public function test_search_status_persisted_in_session()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Item::factory()->create(["item_name" => "Test Item 1", "user_id" => $user->id]);

        $otherSeller = User::factory()->create();
        $itemForMylist = Item::factory()->create([
            "item_name" => "Item for Mylist",
            "user_id" => $otherSeller->id,
        ]);
        $user->likes()->create([
            "item_id" => $itemForMylist->id,
        ]);
        $searchKeyword = "Item";
        $response = $this->get("/?search=" . $searchKeyword);
        $response->assertStatus(200);

        $mylistResponse = $this->getJson('/api/mylist');
        $mylistResponse->assertStatus(200);

        $mylistResponse->assertJsonFragment(['item_name' => 'Item for Mylist']);
        $mylistResponse->assertJsonMissing(['item_name' => 'Test Item 1']);
    }
}
