<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, Item $item)
    {
        $comment = new Comment();
        $comment->item_id = $item->id;
        $comment->user_id = Auth::id();
        $comment->comment = $request->input('comment');
        $comment->save();

        return redirect()->route('items.detail', ['item' => $item->id])->with('success', 'コメントが作成されました。');
    }

    public function destroy(Comment $comment)
    {
        if (Auth::id() !== $comment->user_id) {
            return redirect()->back()->with('error', 'コメントの削除権限がありません。');
        }

        $comment->delete();
        return redirect()->back()->with('success', 'コメントが削除されました。');
    }
}
