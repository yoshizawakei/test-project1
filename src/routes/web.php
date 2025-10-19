<?php

use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\MylistController;
use App\Http\Controllers\MypageController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\PurchasedItemsController;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Route;
use App\Mail\TestMail;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// トップページ関係
Route::get("/", [ItemController::class, "index"])->name("top.index");
Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.detail');

// Item関係
Route::middleware(["auth", "verified"])->group(function () {
    Route::get("/sell", [ItemController::class, "create"])->name("items.create");
    Route::post("/items", [ItemController::class, "store"])->name("items.store");
    Route::get("/items/{item}/edit", [ItemController::class, "edit"])->name("items.edit");
    Route::put("/items/{item}", [ItemController::class, "update"])->name("items.update");
    Route::delete("/items/{item}", [ItemController::class, "destroy"]);

    Route::get("/items/{item}/purchase", [ItemController::class, "purchase"])->name("items.purchase");

    Route::post("/items/{item}/purchase/checkout", [ItemController::class, "createCheckoutSession"])->name("items.createCheckoutSession");

    Route::get("/items/purchase/success", [ItemController::class, "purchaseSuccess"])->name("items.purchaseSuccess");

    Route::get("/items/thanks", function () {
        return view("items.thanks");
    })->name("items.thanks");
});

// mypage関係
Route::middleware("auth")->group(function () {
    Route::get("/mypage", [MypageController::class, "index"])->name("mypage.index");

    // Profile関係
    Route::get("/mypage/profile", [ProfileController::class, "index"])->name("mypage.profile");
    Route::post("profile/edit", [ProfileController::class, "edit"])->name("profile.edit");
    Route::get("profile/address-edit", [ProfileController::class, "addressEdit"])->name("profile.address.edit");
    Route::put("profile/address-update", [ProfileController::class, "addressUpdate"])->name("profile.address.update");
});

// Comment関係
Route::middleware("auth")->group(function () {
    Route::post("/items/{item}/comments", [CommentController::class, "store"])->name("comments.store");
    Route::delete("/comments/{comment}", [CommentController::class, "destroy"])->name("comments.destroy");
});

// Like関係
Route::middleware("auth")->group(function () {
    Route::post("/items/{item}/like", [LikeController::class, "toggle"])->name("items.like.toggle");
    Route::get("/mypage/likes", [LikeController::class, "index"])->name("mypage.likes");
});

// mylist関係
Route::middleware("auth")->get("/api/mylist", [MylistController::class, "index"]);


// メールテスト
Route::get("/send-test-mail", function () {
    $name = "テストユーザー";
    $message_body = "これはLaravelから送信されたテストメールです。";

    Mail::to("test@example")->send(new TestMail($name, $message_body));
    return "Test email sent successfully!";
});

// --- Transaction & Chat & Rating Routes ---
Route::middleware("auth")->group(function () {
    // FN002: 取引チャット表示 (GET)
    Route::get("/transactions/{transaction}/chat", [ChatController::class, "show"])->name("chat.show");

    // FN008, FN009: メッセージ投稿 (POST)
    Route::post("/transactions/{transaction}/messages", [ChatController::class, "store"])->name("chat.message.store");

    // FN010: メッセージ編集 (PUT/PATCH)
    Route::put("/messages/{message}", [ChatController::class, "update"])->name("chat.message.update");

    // FN011: メッセージ削除 (DELETE)
    Route::delete("/messages/{message}", [ChatController::class, "destroy"])->name("chat.message.destroy");

    // FN012, FN013: 評価 (POST)

    // ① 評価のみを行うルート (出品者向け、または取引完了後の評価)
    Route::post("/transactions/{transaction}/rate", [RatingController::class, "store"])->name("transaction.rate.store");

    // ② 取引完了と評価を同時に行うルート (購入者向け)
    Route::post("/transactions/{transaction}/complete-and-rate", [RatingController::class, "completeAndRate"])->name("transaction.complete_and_rate");

    // FN005: 評価平均のAPI
    Route::get("/api/users/{user}/rating/average", [RatingController::class, "average"])->name("api.user.rating.average");

    // FN004: ポーリング用APIルートの追加
    Route::get("/api/transactions/{transaction}/messages", [ChatController::class, "getMessagesApi"])->name("api.chat.messages");

    // 【追加】マイページ「購入した商品」タブ用 API
    Route::get("/api/purchased-items", [PurchasedItemsController::class, "index"])->name("api.purchased.index");
});