<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use App\Models\Item;
use App\Models\Rating;

class MypageController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route("login")->with("error", "ログインしてください。");
        } elseif (!$user->profile_configured) {
            return redirect()->route("mypage.profile")->with("success", "プロフィールを設定してください。");
        }

        $averageRating = Rating::where('rated_user_id', $user->id)
            ->avg('score');

        // 平均評価を小数点第1位に丸める。評価がない場合は 0 とする。
        $averageRating = $averageRating ? round($averageRating, 1) : 0;

        $exhibitedItems = Item::where("user_id", $user->id)->get();

        // 取引中の商品を取得し、最新メッセージ順に並べ替え、未読メッセージ件数を追加する
        $transactions = Transaction::with(['item'])
            ->where(function ($query) use ($user) {
                $query->where('seller_id', $user->id)
                    ->orWhere('buyer_id', $user->id);
            })
            ->where(function ($query) use ($user) {
                // 1. ステータスが 'in_progress' の場合は常に表示
                $query->where('status', 'in_progress')
                    // 2. または、ステータスが 'completed' で、
                    ->orWhere(function ($query) use ($user) {
                    $query->where('status', 'completed')
                        // かつ、自分がその取引の出品者であり、seller_rating_id が NULL の場合 (出品者の評価待ち)
                        ->where('seller_id', $user->id)
                        ->whereNull('seller_rating_id');
                });

                // ※ もし購入者として評価待ちの取引も表示したい場合は、上記の orWhere の外側で追加の orWhere を記述
            })

            ->withMax('messages', 'created_at')

            // 2. 未読メッセージの件数を 'unread_count' として追加
            ->withCount([
                'messages as unread_count' => function ($query) use ($user) {
                    // 相手から送られた（user_idが自分ではない）メッセージで、
                    // かつ read_atがNULL（まだ読んでいない）のものをカウントする
                    $query->where('user_id', '!=', $user->id)
                        ->whereNull('read_at');
                }
            ])

            // 3. 最新メッセージ日時で降順に並べ替え
            ->orderByDesc('messages_max_created_at')

            // 4. 最新メッセージがない場合は取引自体の updated_at で降順に並べ替え
            ->latest('updated_at')

            ->get();

        return view("mypage.index", compact("exhibitedItems", "transactions", "averageRating"));
    }
}