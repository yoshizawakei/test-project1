<?php

namespace App\Http\Controllers;

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

            $imagePath = $request->file("image")->store("items", "public");

            // $imagePath = str_replace("public/", "storage/", $imagePath);
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

    public function update(Request $request, Item $item)
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

    public function createCheckoutSession(Request $request, Item $item)
    {
        \Log::info('createCheckoutSession method called.');
        \Log::info('Request data: ' . json_encode($request->all()));
        \Log::info('Item received from route model binding: ' . $item->id);

        // $item = Item::findOrFail($request->item_id);

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            // 3. Checkout Session のパラメータ準備
            $checkoutSession = Session::create([
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'jpy',
                            'product_data' => [
                                'name' => $item->item_name,
                            ],
                            'unit_amount' => $item->price, // 最小単位（円の場合、円）
                        ],
                        'quantity' => 1,
                    ]
                ],
                'mode' => 'payment', // 商品購入なので 'payment'
                'success_url' => route('items.purchaseSuccess', ['session_id' => '{CHECKOUT_SESSION_ID}']), // ★重要
                'cancel_url' => route('items.detail', ['item' => $item->id]), // 決済キャンセル時のリダイレクト先
                'payment_method_types' => [
                    'card',
                ],
                // メタデータは、決済セッションに関連付ける任意の情報
                // 後で決済成功時にこの情報を使って、DBを更新するなど
                'metadata' => [
                    'item_id' => $item->id,
                    'user_id' => auth()->id(), // 購入ユーザーのIDなど
                ],
            ]);

            // 4. Checkout ページへリダイレクト
            return redirect()->away($checkoutSession->url);

        } catch (\Exception $e) {

            \Log::error('Error in createCheckoutSession: ' . $e->getMessage());
            \Log::error($e->getTraceAsString()); // スタックトレースも出力
            // エラーハンドリング
            return redirect()->back()->with('error', '決済処理中にエラーが発生しました: ' . $e->getMessage());
        }
    }

    // ItemController.php の purchaseSuccess メソッド内
    public function purchaseSuccess(Request $request)
    {
        // 1. URLから session_id を取得
        $sessionId = $request->input('session_id');

        if (!$sessionId) {
            Log::error("PurchaseSuccess: No session_id provided.");
            return redirect()->route('top.index')->with('error', '不正なアクセスです。');
        }

        Stripe::setApiKey(env('STRIPE_SECRET')); // ここでもAPIキーを設定

        try {
            // 2. session_id を使ってStripeからセッション情報を取得
            $session = Session::retrieve($sessionId);

            // 3. 決済ステータスの確認とDB更新
            // コンビニ払いは、この時点では 'open' や 'requires_payment_method' の可能性があります。
            // 厳密な購入確定はWebhookで行うのがベストプラクティスですが、
            // ここでは簡易的に 'paid' を想定しています。
            if ($session->payment_status === 'paid' && $session->mode === 'payment') {
                // メタデータから商品IDとユーザーIDを取得
                $itemId = $session->metadata->item_id ?? null;
                $userId = $session->metadata->user_id ?? null;

                if ($itemId && $userId) {
                    $item = Item::find($itemId);
                    if ($item && $item->sold_at === null) {
                        DB::transaction(function () use ($item, $userId) {
                            $item->update([
                                "sold_at" => now(),
                                "buyer_id" => $userId,
                            ]);
                        });
                        Log::info("Item " . $itemId . " successfully purchased by user " . $userId . " via Stripe Checkout session: " . $sessionId);
                        return view("items.thanks"); // 成功画面表示
                    } elseif ($item && $item->sold_at !== null) {
                        Log::warning("Stripe Checkout success but item " . $itemId . " was already sold for session: " . $sessionId);
                        // すでに販売済みの場合でも、一旦thanksページへ
                        return view("items.thanks")->with("message", "購入は完了していますが、既に商品は売却済みでした。");
                    } else {
                        Log::error("PurchaseSuccess: Item " . $itemId . " not found or could not be updated for session: " . $sessionId);
                        return redirect()->route('top.index')->with('error', '決済は完了しましたが、注文情報の処理に問題が発生しました。');
                    }
                } else {
                    Log::error("PurchaseSuccess: Metadata missing for session: " . $sessionId . " ItemID: " . ($itemId ?? 'null') . " UserID: " . ($userId ?? 'null'));
                    return redirect()->route('top.index')->with('error', '決済は完了しましたが、注文情報の処理に問題が発生しました。');
                }
            } else {
                // 支払いがまだ完了していない場合（コンビニ払いなど）やモードが違う場合
                Log::warning("PurchaseSuccess: Session not paid or wrong mode for session: " . $sessionId . " Status: " . ($session->payment_status ?? 'N/A') . " Mode: " . ($session->mode ?? 'N/A'));
                // コンビニ払いは支払いが確定するまで"paid"にならないため、
                // 支払い待ち画面や、ユーザーに支払いを促すメッセージを表示するのが適切です。
                // テストの状況から、現状はエラーまたはリダイレクトされているため、
                // 一旦 'thanks' ページへリダイレクトさせて問題なければ、後で細分化しましょう。
                return view("items.thanks")->with("message", "支払い処理中です。コンビニでのお支払い完了をお待ちください。");
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error("PurchaseSuccess: Stripe API Error for session " . $sessionId . ": " . $e->getMessage());
            return redirect()->route('top.index')->with('error', '決済情報の取得中にエラーが発生しました。');
        } catch (\Exception $e) {
            Log::critical("PurchaseSuccess: Unexpected error for session " . $sessionId . ": " . $e->getMessage());
            return redirect()->route('top.index')->with('error', '予期せぬエラーが発生しました。');
        }
    }
}
