<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Stripe\Checkout\Session;


class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    public function checkout(Request $request, Item $item)
    {
        if (Auth::id() === $item->user_id) {
            return redirect()->back()->with("error", "自分の商品は購入できません");
        }

        if ($item->sold_at !== null) {
            return redirect()->back()->with("error", "この商品は既に売却済みです");
        }

        $priceInYen = $item->price;

        try {
            $checkoutSession = Session::create([
                "line_items" => [[
                    "price_data" => [
                        "currency" => "jpy",
                        "product_data" => [
                            "name" => $item->item_name,
                            "description" => $item->description,
                        ],
                        "unit_amount" => $priceInYen,
                    ],
                    "quantity" => 1,
                ]],
                "mode" => "payment",
                "success_url" => route("purchase.success") . "?session_id={CHECKOUT_SESSION_ID}",
                "cancel_url" => route("purchase.cancel"),
                "metadata" => [
                    "item_id" => $item->id,
                    "buyer_id" => Auth::id(),
                ],
            ]);

            return redirect()->away($checkoutSession->url);
        } catch (\Exception $e) {
            return redirect()->back()->with("error", "決済処理中にエラーが発生しました: " . $e->getMessage());
        }
    }

    public function cancel()
    {
        return redirect()->route("top.index")->with("error", "決済がキャンセルされました。");
    }

}
