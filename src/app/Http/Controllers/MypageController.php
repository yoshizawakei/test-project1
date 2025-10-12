<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use App\Models\Item;


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

        $exhibitedItems = Item::where("user_id", $user->id)->get();

        // FN001, FN003: 取引中の商品リストを取得
        // Seller または Buyer として参加し、かつ status が 'completed' でない取引
        $transactions = Transaction::with(['item'])
            ->where(function ($query) use ($user) {
                $query->where('seller_id', $user->id)
                    ->orWhere('buyer_id', $user->id);
            })
            ->where('status', '!=', 'completed')
            ->orderByDesc('updated_at') // FN003: 最新のメッセージや更新でソート
            ->get();

        // FN003: 取引通知機能は、このリストをマイページのサイドバー（または専用セクション）に表示することで達成します。
        // updated_at が新しいものを上部に表示することで「別のメッセージ画面」として機能します。

        return view("mypage.index", compact("exhibitedItems", "transactions"));
    }

}
