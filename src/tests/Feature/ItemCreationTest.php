<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;


class ItemCreationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Category::factory()->create(["name" => "テストカテゴリA"]);
        Category::factory()->create(["name" => "テストカテゴリB"]);

        Brand::factory()->create(["name" => "テストブランド"]);

        Storage::fake("public");
    }

    public function test_user_create_item()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $categoryIds = Category::all()->pluck("id")->toArray();
        $brandId = Brand::first()->id;

        $imageFile = UploadedFile::fake()->image("test_item_image.jpg", 100, 100);

        $itemData = [
            "item_name" => "テスト商品名 " . $this->faker->word,
            "description" => $this->faker->sentence(10),
            "image" => $imageFile,
            "category_ids" => [$categoryIds[0]],
            "condition" => "良好",
            "price" => $this->faker->numberBetween(100, 99999),
            "brand_id" => $brandId,
        ];

        $response = $this->post(route("items.store"), $itemData);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $item = Item::firstWhere(["item_name" => $itemData["item_name"]]);
        $this->assertNotNull($item, "Item was not created in the database.");
        $response->assertRedirect(route("top.index"));
        $response->assertSessionHas("success", "商品を出品しました。");

        $this->assertDatabaseHas("items", [
            "user_id" => $user->id,
            "item_name" => $itemData["item_name"],
            "description" => $itemData["description"],
            "condition" => $itemData["condition"],
            "price" => $itemData["price"],
            "brand_id" => $itemData["brand_id"],
            "sold_at" => null,
            "buyer_id" => null,
        ]);

        $this->assertDatabaseHas("category_item", [
            "item_id" => $item->id,
            "category_id" => $itemData["category_ids"][0],
        ]);

        $pathInFakeStorage = $item->image_path;
        if (str_starts_with($pathInFakeStorage, 'public/')) {
            $pathInFakeStorage = substr($pathInFakeStorage, strlen('public/'));
        }
        if (str_starts_with($pathInFakeStorage, 'storage/')) {
            $pathInFakeStorage = substr($pathInFakeStorage, strlen('storage/'));
        }
        Storage::disk("public")->assertExists($pathInFakeStorage);
    }

    public function test_validation_errors_required_fields()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $invalidItemData = [
            "category_ids" => [],
            "price" => -100,
            "brand_id" => null,
        ];

        $response = $this->post(route("items.store"), $invalidItemData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            "item_name",
            "description",
            "image",
            "category_ids",
            "condition",
            "price",
        ]);

        $this->assertDatabaseMissing("items", [
            "user_id" => $user->id,
        ]);
        Storage::disk("public")->assertMissing("item_images");
    }

    public function test_validation_errors_invalid_data()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $existingCategoryIds = Category::all()->pluck("id")->toArray();
        $existingBrandId = Brand::first()->id;

        $invalidFormatData = [
            "item_name" => $this->faker->word,
            "description" => $this->faker->realText(300),
            "image" => UploadedFile::fake()->create("not_an_image.pdf"),
            "category_ids" => [99999],
            "condition" => "不明な状態",
            "price" => "abc",
            "brand_id" => 99999,
        ];

        $response = $this->post(route("items.store"), $invalidFormatData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            "description",
            "image",
            "category_ids.0",
            "condition",
            "price",
            "brand_id",
        ]);
    }
}
