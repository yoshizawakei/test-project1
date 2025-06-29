<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Profile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class UserMyPageTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Set up the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
    }
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_user_profile_and_items_display_mypage()
    {
        $user = User::factory()->create([
            "name" => "テストユーザー",
            "profile_configured" => true,
        ]);

        $fakeProfileImage = UploadedFile::fake()->image("profile.jpg", 100, 100);
        $profileImagePath = $fakeProfileImage->store("profile_images", "public");

        $profile = Profile::factory()->create([
            "user_id" => $user->id,
            "username" => "テストユーザー名",
            "profile_image" => $profileImagePath,
            "postal_code" => "1234567",
            "address" => "東京都新宿区テスト町1-2-3",
            "building_name" => "テストビル",
        ]);

        $this->actingAs($user);

        $exhibitedItems = Item::factory()->count(3)->create([
            "user_id" => $user->id,
            "item_name" => "テスト商品",
            "price" => 1000,
            "description" => "テスト商品説明",
            "sold_at" => null,
            "buyer_id" => null,
        ]);

        $response = $this->get(route('mypage.index'));
        $response->assertOk();

        $response->assertSee(asset("storage/" . $profile->profile_image));

        $response->assertSee($profile->username);

        foreach ($exhibitedItems as $item) {
            $response->assertSee($item->item_name);
            $response->assertSee("¥" . number_format($item->price));
            $response->assertSee($item->image_path);
        }

        $response->assertSee("購入した商品を読み込み中...");

        $response->assertSee(route('mypage.profile'));
        $response->assertSee("プロフィールを編集");
    }

    public function test_purchased_items_display_in_mypage()
    {
        $user = User::factory()->create([
            "name" => "購入者ユーザー",
            "profile_configured" => true,
        ]);
        $this->actingAs($user);

        $seller = User::factory()->create();
        $purchasedItems = Item::factory()->count(3)->create([
            "buyer_id" => $user->id,
            "user_id" => $seller->id,
            "sold_at" => now(),
        ]);

        $response = $this->get(route("mypage.purchased_items"));
        $response->assertOk();

        $response->assertJsonCount(3);
        foreach ($purchasedItems as $item) {
            $response->assertJsonFragment([
                "item_name" => $item->item_name,
                "price" => $item->price,
                "description" => $item->description,
                "image_path" => $item->image_path,
                "condition" => $item->condition,
                "user_id" => $seller->id,
                "buyer_id" => $user->id,
            ]);
        }
    }
}
