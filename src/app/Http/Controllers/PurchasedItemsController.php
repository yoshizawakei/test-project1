<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction; // 取引モデルを起点とする
use App\Models\Item; // 商品モデルをリレーションで使う
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class PurchasedItemsController extends Controller
{
    /**
     * 認証ユーザーが購入した商品と取引情報をJSONで返すAPI
     */
    public function index(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            // 認証されていない場合は401を返す (JS側で処理)
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $userId = Auth::id();

        // 1. Transactionモデルから、ログインユーザーが購入者(buyer_id)となっている取引を取得
        $purchasedTransactions = Transaction::where('buyer_id', $userId)
            ->with('item') // N+1問題対策としてItemをロード
            ->latest('updated_at')
            ->get();

        // 2. JavaScriptが期待する「商品」をメインとした形式にデータを整形する
        $itemsData = $purchasedTransactions->map(function ($transaction) {
            $item = $transaction->item;

            // 商品データが存在しなければスキップ（念のため）
            if (!$item) {
                return null;
            }

            return [
                'id' => $item->id,
                'item_name' => $item->item_name,
                // image_pathはstorage/以降のパスを想定
                'image_path' => $item->image_path,
                'price' => $item->price,
                // sold_atが存在するかどうかで判定
                'sold_out' => $item->sold_at !== null,

                // 【重要】チャット導線に必要な取引情報
                'transaction_id' => $transaction->id,
                'transaction_status' => $transaction->status,
            ];
        })->filter()->values(); // nullを除外し、インデックスをリセット

        return response()->json($itemsData);
    }
}