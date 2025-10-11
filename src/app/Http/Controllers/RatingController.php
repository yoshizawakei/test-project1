<?php

namespace App\Http\Controllers;

use App\Http\Requests\RatingRequest;
use App\Models\Rating;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;   // ★ Mailファサードをインポート
use App\Mail\RatingNotification;       // ★ 作成したMailableをインポート
use Illuminate\Http\JsonResponse;      // ★ JsonResponseをインポート

class RatingController extends Controller
{
    public function store(RatingRequest $request, Transaction $transaction)
    {
        $rater = Auth::user();

        // 評価される側のユーザーIDを特定
        if ($rater->id === $transaction->seller_id) {
            $ratedUserId = $transaction->buyer_id;
        } elseif ($rater->id === $transaction->buyer_id) {
            $ratedUserId = $transaction->seller_id;
        } else {
            abort(403, 'この取引を評価する権限がありません。');
        }

        // 評価される側のユーザー情報を取得
        $ratedUser = User::find($ratedUserId);

        // 既に評価済みでないかチェック
        if (Rating::where('transaction_id', $transaction->id)->where('rater_id', $rater->id)->exists()) {
            return redirect()->back()->with('error', '既にこの取引を評価済みです。');
        }

        $validatedData = $request->validated();

        // 評価レコードの作成
        Rating::create([
            'transaction_id' => $transaction->id,
            'rater_id' => $rater->id,
            'rated_user_id' => $ratedUserId,
            'score' => $validatedData['score'],
            'comment' => $validatedData['comment'],
        ]);

        // ★★★ メール送信処理 ★★★
        try {
            // 評価されたユーザーのメールアドレス宛に通知を送信
            Mail::to($ratedUser->email)->send(new RatingNotification($transaction, $ratedUser, $rater));
        } catch (\Exception $e) {
            // エラーをログに残す
            \Log::error('評価通知メールの送信に失敗しました。', [
                'user_id' => $ratedUser->id,
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);
        }
        // ★★★ メール送信処理ここまで ★★★

        // 取引完了ステータスのチェック
        $ratingsCount = Rating::where('transaction_id', $transaction->id)->count();
        if ($ratingsCount >= 2) { // 2人とも評価したら完了
            $transaction->update(['status' => 'completed', 'completed_at' => now()]);
        }

        return redirect()->back()->with('success', '評価を完了しました。');
    }

    /**
     * FN005: ユーザーの取引評価平均を取得する
     */
    public function average(User $user): JsonResponse
    {
        // ユーザーが受けた評価の平均を計算
        $averageRating = Rating::where('rated_user_id', $user->id)->avg('score');

        return response()->json([
            'user_id' => $user->id,
            'average_rating' => $averageRating ? round($averageRating, 1) : 0, // 小数点第1位まで
            'total_reviews' => Rating::where('rated_user_id', $user->id)->count()
        ]);
    }

}
