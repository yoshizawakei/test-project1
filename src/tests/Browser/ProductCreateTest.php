<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illumminate\Foundation\Testing\WithFaker;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\UploadedFile;

class ProductCreateTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Models\Category|null
     */
    protected ?Category $category1 = null;

    /**
     * @var \App\Models\Category|null
     */
    protected ?Category $category2 = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->category1 = Category::firstOrCreate(['name' => 'テストカテゴリA']);
        $this->category2 = Category::firstOrCreate(['name' => 'テストカテゴリB']);

        $this->assertNotNull($this->category1, 'カテゴリAが作成または取得できませんでした。');
        $this->assertNotNull($this->category2, 'カテゴリBが作成または取得できませんでした。');
    }
    public function testProductInformationCanBeSaved()
    {
        $user = User::factory()->create();
        $brand = Brand::firstOrCreate(['name' => 'テストブランド']);

        Storage::fake('public');
        $Image = UploadedFile::fake()->image('product_image.jpg', 600, 400)->size(100);

        $productData = [
            'image' => $Image,
            'category_ids' => [$this->category1->id, $this->category2->id],
            'condition' => '目立った傷や汚れなし',
            'item_name' => '素晴らしいテスト商品',
            'brand_id' => $brand->id,
            'description' => 'これはテストで出品される商品の詳細な説明です。機能、状態などを記載します。',
            'price' => 29800,
        ];

        $this->browse(function (Browser $browser) use ($user, $productData) {
            $browser->loginAs($user)
                ->visit('/sell')
                ->waitForText("商品の出品", 10)
                ->assertSee('商品の出品')
                ->attach('image', $productData['image']);

            $browser->waitFor('#category-buttons-container', 10);

            foreach ($productData['category_ids'] as $categoryId) {
                $categoryButtonSelector = "button[data-category-id='{$categoryId}']";
                $browser->waitFor($categoryButtonSelector, 10)
                    ->assertVisible($categoryButtonSelector)
                    ->click($categoryButtonSelector);
            }

            $browser->select('condition', $productData['condition'])
                ->type('item_name', $productData['item_name'])
                ->select('brand_id', $productData['brand_id'])
                ->type('description', $productData['description'])
                ->type('price', $productData['price'])
                ->press('出品する')
                ->assertPathIs('/');

            $this->assertDatabaseHas('items', [
                'item_name' => $productData['item_name'],
                'description' => $productData['description'],
                'price' => $productData['price'],
                'condition' => $productData['condition'],
                'brand_id' => $productData['brand_id'],
                'user_id' => $user->id,
            ]);

            $item = Item::where('item_name', $productData['item_name'])->first();
            $this->assertNotNull($item, '商品がデータベースに保存されていません。');

            foreach ($productData['category_ids'] as $categoryId) {
                $this->assertDatabaseHas('category_item', [
                    'item_id' => $item->id,
                    'category_id' => $categoryId,
                ]);
            }

            $this->assertNotNull($item->image_path, '画像のパスがデータベースに保存されていません。');
        });
    }
}
