<?php

namespace Tests\Browser;

// use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Profile;

class PaymentMethodSelectionTest extends DuskTestCase
{
    // use DatabaseMigrations;
    use WithFaker;


    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testPaymentMethodSelectionReflectImmediately()
    {
        $user = User::factory()->create();
        $user->Profile()->create([
            "username" => "テストユーザー",
            "postal_code" => "1234567",
            "address" => "東京都新宿区テスト町1-2-3",
            "building_name" => "テストビル",
        ]);

        $item = Item::factory()->create([
            "item_name" => "テスト商品",
            "price" => 1000,
            "description" => "テスト商品説明",
        ]);

        $this->browse(function (Browser $browser) use ($user, $item) {
            $browser->loginAs($user);

            $browser->visit(route("items.purchase", ["item" => $item->id]))->assertSee($item->item_name)->assertSee("支払い方法");

            $browser->select("payment_method", "convenience_store")->pause(100);
            $browser->assertSeeIn("#selected-payment-method-summary", "コンビニ払い");

            $browser->select("payment_method", "credit_card")->pause(100);
            $browser->assertSeeIn("#selected-payment-method-summary", "カード支払い");

        });
    }
}
