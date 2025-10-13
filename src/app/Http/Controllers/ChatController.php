<?php

namespace App\Http\Controllers;

use App\Http\Requests\MessageRequest;
use App\Models\Transaction;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    public function show(Transaction $transaction)
    {
        $user = Auth::user();

        // 1. ユーザーがこの取引に関わっているか確認
        if ($transaction->seller_id !== $user->id && $transaction->buyer_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        // 2. 現在の取引と関連データをロード
        $transaction->load([
            'item',
            'seller.profile', // 相手のアバター取得のために必要
            'buyer.profile',  // 相手のアバター取得のために必要
            'ratings',
            'messages.user.profile'
        ]);

        // ★★★ 3. サイドバー表示用データ：メッセージの最終更新日時で並べ替え ★★★
        $transactions = Transaction::with('item')
            ->where(function ($query) use ($user) {
                // 売主または買主である取引
                $query->where('seller_id', $user->id)
                    ->orWhere('buyer_id', $user->id);
            })
            ->where('status', '!=', 'completed') // 完了していない取引のみ

            // ★ 新しいメッセージ順に並べ替えるためのクエリ ★
            // 各取引の最新のメッセージの created_at を取得し、それを使って並べ替える
            ->addSelect([
                'last_message_at' => Message::select('created_at')
                    ->whereColumn('transaction_id', 'transactions.id')
                    ->latest()
                    ->limit(1)
            ])
            ->orderByDesc('last_message_at')
            // last_message_at が NULL の場合（まだメッセージがない取引）は取引自体の updated_at 順にする
            ->latest('updated_at')
            ->get();

        // 4. Bladeにデータを渡す
        return view('chat.show', compact('transaction', 'transactions'));
    }

    public function store(MessageRequest $request, Transaction $transaction)
    {
        if (Auth::id() !== $transaction->seller_id && Auth::id() !== $transaction->buyer_id) {
            abort(403, 'この取引にアクセスする権限がありません。');
        }

        $imagePath = null;

        // 画像処理ロジック
        if ($request->hasFile('image')) {
            try {
                $uploadedFile = $request->file('image');

                $fileName = time() . '_' . $uploadedFile->getClientOriginalName();
                $path = $uploadedFile->storeAs('public/chat_images', $fileName);

                $imagePath = str_replace('public/', '', $path);

            } catch (\Exception $e) {
                // 例外発生時、ログ出力を行いデバッグを容易にする
                \Log::error("Chat image upload failed: {$e->getMessage()}", ['user_id' => Auth::id()]);
                return redirect()->back()->withErrors(['image' => '画像の保存中にエラーが発生しました。管理者にご連絡ください。'])->withInput();
            }
        }

        // 厳密に検証済みデータの配列全体を取得
        $validatedData = $request->validated();

        Message::create([
            'transaction_id' => $transaction->id,
            'user_id' => Auth::id(),
            'content' => $validatedData['content'],
            'image_path' => $imagePath,
        ]);

        return redirect()->back()->with('success', 'メッセージが送信されました。');
    }

    public function destroy(Message $message)
    {
        // 1. 認可チェック: 認証ユーザーがメッセージの送信者であるか確認
        if ($message->user_id !== Auth::id()) {
            return response()->json(['error' => 'このメッセージを削除する権限がありません。'], 403);
        }

        try {
            // 2. 画像の削除
            if ($message->image_path) {
                Storage::delete('public/' . $message->image_path);
            }

            // 3. データベースからの削除
            $message->delete();

            // 4. 成功レスポンス (AjaxからのリクエストなのでJSONで返す)
            return response()->json(['success' => true, 'message' => 'メッセージが削除されました。']);

        } catch (\Exception $e) {
            \Log::error("Message deletion failed: {$e->getMessage()}", ['message_id' => $message->id]);
            return response()->json(['error' => 'メッセージの削除中にエラーが発生しました。'], 500);
        }
    }

    public function update(MessageRequest $request, Message $message)
    {
        if (Auth::id() !== $message->user_id) {
            abort(403, 'このメッセージを編集する権限がありません。');
        }

        $message->update($request->validated());

        return redirect()->back()->with('success', 'メッセージが更新されました。');
    }

    public function getMessagesApi(Transaction $transaction)
    {
        if (Auth::id() !== $transaction->seller_id && Auth::id() !== $transaction->buyer_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // メッセージと送信者情報をEager Load
        $messages = $transaction->messages()
            ->with('user.profile')
            ->orderBy('created_at', 'asc')
            ->get();

        // BladeテンプレートをレンダリングしてHTMLとして返す (推奨)
        $html = view('chat.messages_list', compact('messages'))->render();

        return response()->json(['html' => $html]);
    }
}