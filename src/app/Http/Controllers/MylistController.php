<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Like;
use App\Models\Item;

class MylistController extends Controller
{
    /**
     * Display the user's mylist.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(["message" => "Unauthorized"], 401);
        }

        $likedItemIds = Like::where('user_id', $user->id)->pluck('item_id');

        $items = Item::with(["user", "categories", "brand"])
            ->whereIn('id', $likedItemIds)
            ->where('user_id', '!=', $user->id)
            ->get();

        return response()->json($items);
    }
}
