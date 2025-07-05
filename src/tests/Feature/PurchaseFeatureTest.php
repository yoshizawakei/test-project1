<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Profile;
use Mockery;

class PurchaseFeatureTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $mockRetrieveSessionData = [];

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.stripe.secret' => 'sk_test_mock_secret_key']);

        $mockStripeCheckoutSession = Mockery::mock('alias:' . \Stripe\Checkout\Session::class);

        $mockStripeCheckoutSession->shouldReceive('create')
            ->andReturn((object) [
                'url' => route('items.purchaseSuccess', ['session_id' => 'dummy_session_id'], false),
                'id' => 'cs_dummy_id_for_create',
            ])
            ->byDefault();

        $mockStripeCheckoutSession->shouldReceive('retrieve')
            ->with("dummy_session_id")
            ->andReturnUsing(function () {
                return (object) array_merge([
                    'id' => 'dummy_session_id',
                    'payment_status' => 'paid',
                    'mode' => 'payment',
                    'metadata' => (object) [],
                ], $this->mockRetrieveSessionData);
            })
            ->byDefault();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function test_user_purchase_item()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create([
            'email_verified_at' => \Carbon\Carbon::now(),
            'profile_configured' => true,
        ]);
        Profile::factory()->create(["user_id" => $buyer->id]);

        $itemToPurchase = Item::factory()->create(["user_id" => $seller->id, "sold_at" => null]);

        $this->mockRetrieveSessionData = [
            "metadata" => (object) [
                "item_id" => $itemToPurchase->id,
                "user_id" => $buyer->id,
            ],
        ];

        $this->actingAs($buyer);

        $purchaseData = [
            'item_id' => $itemToPurchase->id,
            'payment_method' => 'credit_card',
            'user_profile_exists' => '1',
        ];

        $response = $this->post(route("items.createCheckoutSession", $itemToPurchase), $purchaseData);

        $expectedRedirectUrl = route('items.purchaseSuccess', ['session_id' => 'dummy_session_id'], false);
        $response->assertRedirect($expectedRedirectUrl);

        $successResponse = $this->actingAs($buyer)->get($expectedRedirectUrl);
        $successResponse->assertStatus(200);
        $successResponse->assertViewIs("items.thanks");

        $itemToPurchase->refresh();
        $this->assertNotNull($itemToPurchase->sold_at);
        $this->assertEquals($buyer->id, $itemToPurchase->buyer_id);

        $this->get(route('top.index'))
            ->assertSee($itemToPurchase->item_name);
    }

    /**
     * @test
     */
    public function purchased_item_is_displayed_as_sold_on_item_list()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create([
            'email_verified_at' => \Carbon\Carbon::now(),
            'profile_configured' => true,
        ]);
        Profile::factory()->create(["user_id" => $buyer->id]);

        $itemToPurchase = Item::factory()->create(['user_id' => $seller->id, 'sold_at' => null]);

        $this->mockRetrieveSessionData = [
            "metadata" => (object) [
                "item_id" => $itemToPurchase->id,
                "user_id" => $buyer->id,
            ],
        ];

        $this->actingAs($buyer)->post(route("items.createCheckoutSession", $itemToPurchase), [
            "payment_method" => "credit_card",
            "user_profile_exists" => '1',
        ]);
        $this->actingAs($buyer)->get(route("items.purchaseSuccess", ['session_id' => 'dummy_session_id']));

        $response = $this->get(route('top.index'));
        $response->assertStatus(200);
        $response->assertSee($itemToPurchase->item_name);
    }

    /**
     * @test
     */
    public function purchased_item_is_added_to_purchased_items_list_on_profile()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create([
            'email_verified_at' => \Carbon\Carbon::now(),
            'profile_configured' => true,
        ]);
        Profile::factory()->create(["user_id" => $buyer->id]);

        $itemToPurchase = Item::factory()->create(['user_id' => $seller->id, 'sold_at' => null]);

        $this->mockRetrieveSessionData = [
            "metadata" => (object) [
                "item_id" => $itemToPurchase->id,
                "user_id" => $buyer->id,
            ],
        ];

        $this->actingAs($buyer)->post(route("items.createCheckoutSession", $itemToPurchase), [
            "payment_method" => "credit_card",
            "user_profile_exists" => '1',
        ]);
        $this->actingAs($buyer)->get(route("items.purchaseSuccess", ['session_id' => 'dummy_session_id']));

        $response = $this->actingAs($buyer)->getJson(route('mypage.purchased_items'));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $itemToPurchase->id,
            'item_name' => $itemToPurchase->item_name,
        ]);
    }
}