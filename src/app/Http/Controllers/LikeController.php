<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Like;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function toggle(Item $item)
    {
        $user = Auth::user();

        // すでにいいねがあるか確認
        $like = Like::where("user_id", $user->id)
            ->where("item_id", $item->id)
            ->first();

        if ($like) {
            // すでにいいねがある場合は解除
            $like->delete();
            $message = "いいねを解除しました";
            $liked = false;
        } else {
            // いいねがなければ登録
            Like::create([
                "user_id" => $user->id,
                "item_id" => $item->id,
            ]);
            $message = "いいねしました";
            $liked = true;
        }

        if (request()->ajax()) {
            return response()->json([
                "message" => $message,
                "liked" => $liked,
                "like_count" => $item->likes()->count(),
            ]);
        }
        return redirect()->back()->with("success", $message);
    }

    public function index()
    {
        $user = Auth::user();

        $likedItems = $user->likes()->with("item")->get()->map(function ($like) {
            return $like->item;
        });

        return view("mypage.likes", compact("likedItems"));
    }
}
