<?php

use App\Http\Controllers\ItemController;
use App\Http\Controllers\AuthController;
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

Route::middleware('auth')->group(function () {
    
});

Route::get("/sent-test-mail", function () {
    Mail::to("kei_yszwa_2525@yahoo.co.jp")->send(new TestMail());
    return "Test email sent!";
});