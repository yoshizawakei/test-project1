<?php

namespace App\Http\Controllers;

use App\Http\Requests\RatingRequest;
use App\Models\Rating;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\RatingNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class RatingController extends Controller
{
    /**
     * 【出品者向け/完了後】評価のみを保存する
     * transaction.rate.store に対応
     */
    public function store(RatingRequest $request, Transaction $transaction)
    {
        // storeメソッドは変更なし
        $rater = Auth::user();

        if (Rating::where('transaction_id', $transaction->id)->where('rater_id', $rater->id)->exists()) {
            return redirect()->back()->with('error', '既にこの取引を評価済みです。');
        }

        $ratedUser = ($rater->id === $transaction->seller_id) ? $transaction->buyer : $transaction->seller;
        $ratedUserId = $ratedUser->id;
        $updateColumn = ($rater->id === $transaction->seller_id) ? 'seller_rating_id' : 'buyer_rating_id';
        $validatedData = $request->validated();

        DB::transaction(function () use ($transaction, $rater, $ratedUserId, $validatedData, $ratedUser, $updateColumn) {
            $rating = Rating::create([
                'transaction_id' => $transaction->id,
                'rater_id' => $rater->id,
                'rated_user_id' => $ratedUserId,
                'score' => $validatedData['score'],
                'comment' => $validatedData['comment'],
            ]);

            $transaction->update([
                $updateColumn => $rating->id,
            ]);

            try {
                Mail::to($ratedUser->email)->send(new RatingNotification($transaction, $ratedUser, $rater));
            } catch (\Exception $e) {
                \Log::error('評価通知メールの送信に失敗しました。', ['user_id' => $ratedUser->id, 'error' => $e->getMessage()]);
            }
        });

        return redirect()->route('chat.show', $transaction)->with('success', '評価を送信しました。');
    }

    /**
     * 【購入者向け】取引ステータスを完了にし、評価を保存する
     * transaction.complete_and_rate に対応
     */
    public function completeAndRate(RatingRequest $request, Transaction $transaction)
    {
        $rater = Auth::user();

        // 1. 認可チェック: 購入者のみが実行できる
        if ($transaction->buyer_id !== $rater->id) {
            abort(403, '取引完了は購入者のみ実行可能です。');
        }

        // 2. 既に完了済みでないかチェック
        if ($transaction->status === 'completed') {
            return redirect()->back()->with('error', '既に取引は完了しています。');
        }

        // 3. 購入者が既に評価済みでないかチェック
        if (Rating::where('transaction_id', $transaction->id)->where('rater_id', $rater->id)->exists()) {
            return redirect()->back()->with('error', '既に評価済みのため、取引ステータスのみが更新されます。');
        }

        $ratedUser = $transaction->seller;
        $validatedData = $request->validated();


        try {
            DB::transaction(function () use ($transaction, $rater, $ratedUser, $validatedData) {

                // A. 取引ステータスを完了に更新
                // ★ 修正点: $transaction->refresh() を削除し、シンプルな更新に戻す ★
                $transaction->update(['status' => 'completed', 'completed_at' => Carbon::now()]);

                // B. 評価を保存
                $rating = Rating::create([
                    'transaction_id' => $transaction->id,
                    'rater_id' => $rater->id, // 購入者
                    'rated_user_id' => $ratedUser->id, // 出品者
                    'score' => $validatedData['score'],
                    'comment' => $validatedData['comment'],
                ]);

                // C. 取引テーブルの評価済みフラグ（ID）を更新
                $transaction->update([
                    'buyer_rating_id' => $rating->id, // 購入者が評価したので buyer_rating_id を更新
                ]);

                // D. メール送信 (評価通知)
                try {
                    Mail::to($ratedUser->email)->send(new RatingNotification($transaction, $ratedUser, $rater));
                } catch (\Exception $e) {
                    \Log::error('評価通知メールの送信に失敗しました。', ['user_id' => $ratedUser->id, 'error' => $e->getMessage()]);
                }
            });

        } catch (\Exception $e) {
            return redirect()->back()->with('error', '評価と取引完了中に致命的なエラーが発生しました。エラーログを確認してください。');
        }

        // 取引完了後、チャット画面ではなくマイページに戻るのが一般的
        return redirect()->route('mypage.index')->with('success', '取引を完了し、出品者への評価を送信しました。');
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