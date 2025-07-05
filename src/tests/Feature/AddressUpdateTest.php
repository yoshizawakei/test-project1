<?php

namespace Tests\Feature;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;
use Mockery;
use Stripe\Checkout\Session;

class AddressUpdateTest extends TestCase
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

        Mockery::mock('alias:' . Session::class)
            ->shouldReceive('create')
            ->andReturn((object) ['url' => 'https://checkout.stripe.com/pay/mock_session_id'])
            ->byDefault();

        User::factory()->create();

        Item::factory()->create([
            "item_name" => "テスト商品A",
            "price" => 1000,
            "description" => "これはテスト商品Aです。",
            "user_id" => User::factory()->create()->id,
            "sold_at" => null,
            "buyer_id" => null,
        ]);
    }

    public function test_registered_address_reflects_on_product_purchase_screen()
    {
        $user = User::factory()->create(["name" => "テスト太郎"]);
        $this->actingAs($user);

        $newAddressData = [
            "username" => "テストユーザー名",
            "postal_code" => "123-4567",
            "address" => "東京都渋谷区青山1-2-3",
            "building_name" => "テストビルディング",
        ];

        $storedPostalCode = str_replace("-", "", $newAddressData["postal_code"]);

        $response = $this->put(route("profile.address.update"), $newAddressData);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route("mypage.index"));

        $this->assertDatabaseHas("profiles", [
            "user_id" => $user->id,
            "username" => $newAddressData["username"],
            "postal_code" => $storedPostalCode,
            "address" => $newAddressData["address"],
            "building_name" => $newAddressData["building_name"],
        ]);

        $item = Item::first();
        $this->assertNotNull($item, "テスト商品が存在しません。setUpメソッドを確認してください。");

        $response = $this->get(route("items.purchase", $item));

        $response->assertOk();

        $displayedPostalCode = substr($storedPostalCode, 0, 3) . "-" . substr($storedPostalCode, 3);

        $response->assertSeeText("〒" . $displayedPostalCode);
        $response->assertSee($newAddressData["address"]);
        if (!empty($newAddressData["building_name"])) {
            $response->assertSee($newAddressData["building_name"]);
        }
    }

    public function test_purchase_links_to_registered_address()
    {
        $user = User::factory()->create(["name" => "購入者太郎"]);
        $this->actingAs($user);

        $addressData = [
            "username" => "購入者ユーザー名",
            "postal_code" => "987-6543",
            "address" => "大阪府大阪市北区梅田1-1-1",
            "building_name" => "テストマンション",
        ];

        $storedPostalCode = str_replace("-", "", $addressData["postal_code"]);

        $itemToPurchase = Item::whereNull("sold_at")->first();
        $this->assertNotNull($itemToPurchase, "未購入のテスト商品が存在しません。setUpメソッドを確認してください。");

        $response = $this->put(route("profile.address.update", ["item_id" => $itemToPurchase->id]), $addressData);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route("items.purchase", ["item" => $itemToPurchase->id]));

        $this->assertDatabaseHas("profiles", [
            "user_id" => $user->id,
            "username" => $addressData["username"],
            "postal_code" => $storedPostalCode,
            "address" => $addressData["address"],
            "building_name" => $addressData["building_name"],
        ]);

        $purchaseData = [
            "payment_method" => "credit_card",
            "user_profile_exists" => "1",
        ];

        $response = $this->post(route("items.createCheckoutSession", $itemToPurchase), $purchaseData);

        $response->assertSessionHasNoErrors();

        $response->assertStatus(302);
        $response->assertRedirectContains("https://checkout.stripe.com");
    }
}
