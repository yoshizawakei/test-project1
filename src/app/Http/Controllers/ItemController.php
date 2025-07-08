<?php

namespace App\Http\Controllers;

use Facade\Ignition\Exceptions\ViewException;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\User;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\ExhibitionRequest;
use App\Http\Requests\PurchaseRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Illuminate\Support\Facades\URL;


class ItemController extends Controller
{
    public function index(Request $request)
    {
        $itemsQuery = Item::with(['user', 'categories', 'brand']);

        if (Auth::check()) {
            $itemsQuery->where('user_id', '!=', Auth::id());
        }

        // 検索機能
        $searchQuery = $request->input("search");

        if ($searchQuery) {
            $itemsQuery->where(function ($q) use ($searchQuery) {
                $q->where("item_name", "like", "%{$searchQuery}%")
                    ->orWhere("description", "like", "%{$searchQuery}%");
            });
        } else {
            $itemsQuery->inRandomOrder();
        }

        $items = $itemsQuery->get();

        return view("top.index", compact("items"));
    }

    public function show(Item $item)
    {
        $item = Item::with(['user', 'categories', 'brand'])->findOrFail($item->id);
        return view("items.detail", [
            "item" => $item,
            "user" => $item->user,
            "brand" => $item->brand,
        ]);
    }

    public function create()
    {
        $categories = Category::all();
        $brands = Brand::all();
        
        return view("items.sell", compact("categories", "brands"));
    }

    public function store(ExhibitionRequest $request)
    {
        $imagePath = null;
        if ($request->hasFile("image")) {

            $imagePath = $request->file("image")->store("public/items");

            $imagePath = str_replace("public/", "storage/", $imagePath);
        }

        $userId = Auth::id();
        $item = null;

        DB::transaction(function () use ($request, $imagePath, $userId, &$item) {
            $item = Item::create([
                "item_name" => $request->item_name,
                "price" => $request->price,
                "description" => $request->description,
                "image_path" => $imagePath,
                "condition" => $request->condition,
                "user_id" => $userId,
                "brand_id" => $request->brand_id,
                // "sold_at" => null,
                // "buyer_id" => null,
            ]);

            $item->categories()->sync($request->category_ids);
        });

        return redirect()->route("top.index")->with("success", "商品を出品しました。");
    }

    public function edit(Item $item)
    {
        if (Auth::id() !== $item->user_id) {
            abort(403, "Unauthorized action.");
        }

        $categories = Category::all();
        $brands = Brand::all();
        $conditions = [
            "良好",
            "目立った傷や汚れなし",
            "やや傷や汚れあり",
            "状態が悪い",
        ];

        $item->load("categories");

        return view("items.edit", compact("item", "categories", "brands", "conditions"));
    }

    public function update(ExhibitionRequest $request, Item $item)
    {
        if (Auth::id() !== $item->user_id) {
            abort(403, "Unauthorized action.");
        }

        $imagePath = $item->image_path;

        if ($request->hasFile("image")) {
            if ($item->image_path) {
                Storage::delete(str_replace("storage/", "public/", $item->image_path));
            }
            $imagePath = $request->file("image")->store("public/items");
            $imagePath = str_replace("public/", "storage/", $imagePath);
        }

        $item->update([
            "item_name" => $request->item_name,
            "price" => $request->price,
            "description" => $request->description,
            "image_path" => $imagePath,
            "condition" => $request->condition,
            "brand_id" => $request->brand_id,
        ]);

        $item->categories()->sync($request->category_ids);

        return redirect()->route("items.detail",$item)->with("success", "商品情報を更新しました。");
    }

    public function destroy(Item $item)
    {
        if (Auth::id() !== $item->user_id) {
            abort(403, "Unauthorized action.");
        }

        if ($item->image_path) {
            Storage::delete(str_replace("storage/", "public/", $item->image_path));
        }

        $item->delete();

        return redirect()->route("top.index")->with("success", "商品を削除しました。");
    }

    public function purchase(Item $item)
    {
        if (Auth::id() === $item->user_id) {
            abort(403, "自分の商品は購入できません。");
        }
        if ($item->sold_at !== null) {
            return redirect()->route("items.detail", $item)->with("error", "この商品はすでに購入されています。");
        }
        return view("items.purchase", compact("item"));
    }

    public function createCheckoutSession(PurchaseRequest $request, Item $item)
    {
        \Log::info('createCheckoutSession method called.');
        \Log::info('Request data: ' . json_encode($request->all()));
        \Log::info('Item received from route model binding: ' . $item->id);

        // 配送先が設定されているかどうかのバリデーション (フォームリクエストを使用しない場合)
        // もしPurchaseRequestを使用するなら、このロジックはフォームリクエストに移動
        if ($request->input('user_profile_exists') === '0') {
            return redirect()->back()->withErrors(['user_profile_exists' => '配送先情報が設定されていません。'])->withInput();
        }

        $selectedPaymentMethod = $request->input('payment_method');
        \Log::info('Selected Payment Method: ' . $selectedPaymentMethod);

        // Stripe APIキーを設定
        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $paymentMethodTypes = [];
            $paymentMethodOptions = [];

            $billingAddressCollection = 'required';

            if ($selectedPaymentMethod === 'credit_card') {
                $paymentMethodTypes[] = 'card';
            } elseif ($selectedPaymentMethod === 'convenience_store') {
                $paymentMethodTypes[] = 'konbini'; // コンビニ決済タイプを追加
                // コンビニ決済に必要なオプション
                $paymentMethodOptions['konbini'] = [
                    'expires_after_days' => 3,
                ];
            } else {
                // 無効な支払い方法が選択された場合
                return redirect()->back()->with('error', '有効な支払い方法が選択されていません。')->withInput();
            }

            $checkoutSession = Session::create([
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'jpy',
                            'product_data' => [
                                'name' => $item->item_name,
                            ],
                            'unit_amount' => $item->price,
                        ],
                        'quantity' => 1,
                    ]
                ],
                'mode' => 'payment',
                'success_url' => route('items.purchaseSuccess', ['session_id' => '{CHECKOUT_SESSION_ID}']),
                'cancel_url' => route('items.detail', ['item' => $item->id]),
                'payment_method_types' => $paymentMethodTypes, // 動的に設定
                'payment_method_options' => $paymentMethodOptions, // コンビニ決済用オプション
                'metadata' => [
                    'item_id' => $item->id,
                    'user_id' => auth()->id(),
                    'payment_method_chosen' => $selectedPaymentMethod, // どの支払い方法が選択されたかをメタデータに保存
                ],
                'billing_address_collection' => $billingAddressCollection, // 請求先住所の収集を必須に設定
            ]);

            // Checkout ページへリダイレクト
            return redirect()->away($checkoutSession->url);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            \Log::error('Stripe API Error in createCheckoutSession: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', '決済サービスとの連携中に問題が発生しました: ' . $e->getMessage())->withInput();
        } catch (\Exception $e) {
            \Log::error('General Error in createCheckoutSession: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', '購入処理中に予期せぬエラーが発生しました。')->withInput();
        }
    }

    /**
     * Stripe Checkout 成功後のリダイレクト先
     * 決済ステータスを確認し、DBを更新、thanksページへ遷移
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function purchaseSuccess(Request $request)
    {
        $sessionId = $request->input('session_id');

        if (!$sessionId) {
            \Log::error("PurchaseSuccess: No session_id provided.");
            return redirect()->route('top.index')->with('error', '不正なアクセスです。');
        }

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $session = \Stripe\Checkout\Session::retrieve($sessionId);
            \Log::info('Retrieved Stripe Session in purchaseSuccess: ' . json_encode($session));

            $paymentMethodChosen = $session->metadata->payment_method_chosen ?? 'unknown';

            // 決済ステータスのチェックとDB更新 (今回は模擬的に、Checkout完了時点で更新)
            // 'paid' の場合（主にカード決済）または 'requires_payment_method' でコンビニ決済の場合にDB更新
            if (
                ($session->payment_status === 'paid' && $session->mode === 'payment') ||
                ($paymentMethodChosen === 'convenience_store' && $session->payment_status === 'requires_payment_method')
            ) {

                $itemId = $session->metadata->item_id ?? null;
                $userId = $session->metadata->user_id ?? null;

                if ($itemId && $userId) {
                    $item = \App\Models\Item::find($itemId);

                    if ($item && $item->sold_at === null) {
                        \DB::transaction(function () use ($item, $userId, $paymentMethodChosen) {
                            $item->update([
                                "sold_at" => now(),
                                "buyer_id" => $userId,
                                "payment_method" => $paymentMethodChosen, // 決済方法を保存
                            ]);
                        });
                        \Log::info("Item " . $itemId . " successfully purchased by user " . $userId . " via Stripe Checkout session: " . $sessionId . " (DB updated)");
                        return view("items.thanks"); // 成功画面表示
                    } elseif ($item && $item->sold_at !== null) {
                        \Log::warning("Stripe Checkout success but item " . $itemId . " was already sold for session: " . $sessionId . " (DB not updated)");
                        return view("items.thanks")->with("message", "購入は完了していますが、既に商品は売却済みでした。");
                    } else {
                        \Log::error("PurchaseSuccess: Item " . $itemId . " not found or could not be updated for session: " . $sessionId . " (DB update failed)");
                        // アイテムが見つからない、または更新できない場合でも、とりあえずthanksページに留める
                        return view("items.thanks")->with('error', '決済は完了しましたが、注文情報の処理に問題が発生しました。');
                    }
                } else {
                    \Log::error("PurchaseSuccess: Metadata missing for session: " . $sessionId . " ItemID: " . ($itemId ?? 'null') . " UserID: " . ($userId ?? 'null') . " (DB update skipped)");
                    return view("items.thanks")->with('error', '決済は完了しましたが、注文情報の処理に問題が発生しました。');
                }
            } else {
                // 'paid' でもなく 'requires_payment_method' (konbini) でもない、
                // その他の支払い処理中または不明なステータスの場合
                \Log::info("PurchaseSuccess: Session status not finalized for session: " . $sessionId . " Status: " . ($session->payment_status ?? 'N/A') . " Mode: " . ($session->mode ?? 'N/A') . " Chosen: " . $paymentMethodChosen);
                return view("items.thanks")->with("message", "ご注文ありがとうございます！支払い処理中です。完了次第ご連絡いたします。");
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            \Log::error("PurchaseSuccess: Stripe API Error for session " . $sessionId . ": " . $e->getMessage());
            return redirect()->route('top.index')->with('error', '決済情報の取得中にエラーが発生しました。');
        } catch (\Exception $e) {
            \Log::critical("PurchaseSuccess: Unexpected error for session " . $sessionId . ": " . $e->getMessage());
            return redirect()->route('top.index')->with('error', '予期せぬエラーが発生しました。');
        }
    }
}
