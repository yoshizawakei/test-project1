<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ItemListTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_Items_all()
    {
        Item::factory()->count(3)->create();

        $response = $this->get('/');

        $response->assertStatus(200);

        $items = Item::all();
        foreach ($items as $item) {
            $response->assertSeeText($item->item_name);
        }
    }

    public function test_sold_Item()
    {
        item::factory()->create(['item_name' => 'Available Item', 'sold_at' => null]);
        item::factory()->create(['item_name' => 'Sold Item', 'sold_at' => now(), 'buyer_id' => User::factory()->create()->id]);

        $response = $this->get('/');

        $response->assertStatus(200);

        // 未購入商品名が表示され、「Sold」の表示がないことを確認
        $response->assertSeeText('Available Item');
        $response->assertDontSeeText('Available Item (SOLD)');

        // 購入済み商品名と「Sold」の表示があることを確認
        $response->assertSeeText('Sold Item');
        $response->assertSeeText('SOLD');
    }

    public function test_own_Item_not_displayed()
    {
        $loggedInUser = User::factory()->create();

        item::factory()->create(['user_id' => $loggedInUser->id, 'item_name' => 'My Own Item']);

        $otherUser = User::factory()->create();
        item::factory()->create(['user_id' => $otherUser->id, 'item_name' => 'Other User\'s Item']);
        item::factory()->create(['user_id' => $otherUser->id, 'item_name' => 'Another User\'s Item']);

        $response = $this->actingAs($loggedInUser)->get('/');

        $response->assertStatus(200);

        $response->assertDontSeeText('My Own Item');

        $response->assertSeeText('Other User\'s Item');
        $response->assertSeeText('Another User\'s Item');
    }
}
