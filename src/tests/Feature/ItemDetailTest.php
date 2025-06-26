<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Like;

class ItemDetailTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_item_detail_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $brand = Brand::factory()->create(['name' => 'Test Brand']);

        $category1 = Category::factory()->create(['name' => 'Test Category']);
        $category2 = Category::factory()->create(['name' => 'Another Category']);

        $item = Item::factory()->create([
            'item_name' => 'Test Item',
            "price" => 10000,
            'description' => 'This is a test item description.',
            "condition" => "良好",
            'brand_id' => $brand->id,
            "image_path" => "test_item.jpg",
            "user_id" => User::factory()->create()->id,
        ]);
        $item->categories()->attach([$category1->id, $category2->id]);

        Like::factory()->create([
            'item_id' => $item->id,
            'user_id' => $user->id,
        ]);

        $commentUser1 = User::factory()->create(['name' => 'Comment User 1']);
        $commentUser2 = User::factory()->create(['name' => 'Comment User 2']);
        Comment::factory()->create(["user_id" => $commentUser1->id, "item_id" => $item->id, "comment" => "This is a comment from User 1."]);
        Comment::factory()->create(["user_id" => $commentUser2->id, "item_id" => $item->id, "comment" => "This is a comment from User 2."]);

        // 詳細ページにアクセス
        $response = $this->get("/items/{$item->id}");
        $response->assertStatus(200);
        // 商品画像の表示
        $response->assertSee(asset($item->image_path));
        // 商品名
        $response->assertSeeText("Test Item");
        // ブランド名
        $response->assertSeeText("Test Brand");
        // 価格
        $response->assertSeeText("¥10,000");
        // 商品の説明
        $response->assertSeeText("This is a test item description.");
        // 商品の状態
        $response->assertSeeText("良好");
        // いいねの数
        $response->assertSeeText("1");
        // コメントの数
        $response->assertSeeText("2");
        // カテゴリの表示
        $response->assertSeeText("Test Category");
        $response->assertSeeText("Another Category");
        // コメントの表示
        $response->assertSeeText("Comment User 1");
        $response->assertSeeText("This is a comment from User 1.");
        $response->assertSeeText("Comment User 2");
        $response->assertSeeText("This is a comment from User 2.");
    }

    public function test_multiple_categories_displayed()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category1 = Category::factory()->create(['name' => 'Test Category']);
        $category2 = Category::factory()->create(['name' => 'Another Category']);
        $category3 = Category::factory()->create(['name' => 'Third Category']);

        $brand = Brand::factory()->create(['name' => 'Test Brand']);

        $item = Item::factory()->create([
            'item_name' => 'Test Item',
            "price" => 10000,
            'description' => 'This is a test item description.',
            "condition" => "良好",
            'brand_id' => $brand->id,
            "image_path" => "test_item.jpg",
            "user_id" => User::factory()->create()->id,
        ]);
        $item->categories()->attach([$category1->id, $category2->id, $category3->id]);

        // 詳細ページにアクセス
        $response = $this->get("/items/{$item->id}");
        $response->assertStatus(200);
        // カテゴリの表示
        $response->assertSeeText("Test Category");
        $response->assertSeeText("Another Category");
        $response->assertSeeText("Third Category");
    }

}
