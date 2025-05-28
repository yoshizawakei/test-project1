<?php

use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProfileController;
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

Route::get("/", [ItemController::class, "index"]);
Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.detail');

Route::middleware(["auth", "verified"])->group(function () {
    Route::get("/items/create", [ItemController::class, "create"]);
    Route::post("/items", [ItemController::class, "store"]);
    Route::get("/items/{item}/edit", [ItemController::class, "edit"]);
    Route::put("/items/{item}", [ItemController::class, "update"]);
    Route::delete("/items/{item}", [ItemController::class, "destroy"]);
});

Route::get("/profile/mypage", [ProfileController::class, "index"])->name("profile.mypage");
Route::post("profile/edit", [ProfileController::class, "edit"])->name("profile.edit");