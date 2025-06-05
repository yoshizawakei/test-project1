<?php

use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\MylistController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
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
Route::get("/", [ItemController::class, "index"]);
Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.detail');

// Item関係
Route::middleware(["auth", "verified"])->group(function () {
    Route::get("/items/create", [ItemController::class, "create"]);
    Route::post("/items", [ItemController::class, "store"]);
    Route::get("/items/{item}/edit", [ItemController::class, "edit"]);
    Route::put("/items/{item}", [ItemController::class, "update"]);
    Route::delete("/items/{item}", [ItemController::class, "destroy"]);
});

// Profile関係
Route::get("/profile/mypage", [ProfileController::class, "index"])->name("profile.mypage");
Route::post("profile/edit", [ProfileController::class, "edit"])->name("profile.edit");


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