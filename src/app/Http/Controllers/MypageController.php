<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\User;

class MypageController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route("login")->with("error", "ログインしてください。");
        }

        $exhibitedItems = Item::whereNull("buyer_id")->get();

        return view("mypage.index", compact("exhibitedItems"));
    }

    public function getPurchasedItems(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(["error" => "ログインしてください。"], 401);
        }

        $purchasedItems = Item::where("buyer_id", $user->id)
            ->whereNotNull("buyer_id")
            ->get();

        return response()->json($purchasedItems);
    }
}
