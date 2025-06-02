@extends('layouts.app')

@section("css")
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
    <div class="container">
        <div class="product-image">
            <img src="{{ $item["image_path"] }}" alt="商品画像">
        </div>
        <div class="product-details">
            <h1 class="product-title">{{ $item["item_name"] }}</h1>
            <p class="brand-name">ブランド名：{{ $item->brand->name }}</p>
            <div class="price-info">
                <span class="price">¥{{ number_format($item["price"]) }}</span> <span class="tax">(税込)</span>
            </div>
            <div class="comment-like_list">
                <div class="like-button-wrapper">
                    <button id="like-button"
                            class="btn {{ Auth::check() && Auth::user()->isLiking($item) ? 'btn-danger' : 'btn-outline-secondary' }}"
                            data-item-id="{{ $item->id }}"
                            data-liked="{{ Auth::check() && Auth::user()->isLiking($item) ? 'true' : 'false' }}"
                            data-logged-in="{{ Auth::check() ? 'true' : 'false' }}">
                        <i class="fas fa-heart"></i>
                        <span id="like-text">
                            @if (Auth::check())
                                {{ Auth::user()->isLiking($item) ? 'いいね済み' : 'いいね' }}
                            @else
                                いいね
                            @endif
                        </span>
                    </button>
                    <span id="likes-count" class="like-count-display">{{ $item->likesCount() }}</span>
                </div>
                <div class="comment-count">
                    <span class="comment-icon">
                        <img src="{{ asset("img/comment_icon.png") }}" alt="comment">
                    </span>
                    <span class="comment-count">{{ $item->comments->count() }}</span>
                </div>
            </div>
            <button class="purchase-button">購入手続きへ</button>

            <div class="product-description">
                <h2>商品説明</h2>
                <ul>
                    <li>カラー：<span class="detail-value">{{ $item->color->name }}</span></li>
                    <li>状態：<span class="detail-value">{{ $item->status->name }}</span></li>
                    <li>詳細：<span class="detail-value">{{ $item["description"] }}</span></li>
                </ul>
            </div>

            <div class="product-info">
                <h2>商品の情報</h2>
                <ul>
                    <li>カテゴリー：<span class="detail-value">{{ $item->category->name }}</span>, <span class="detail-value">{{ $item->category->name }}</span></li>
                    <li>商品の状態：<span class="detail-value">{{ $item["condition"] }}</span></li>
                </ul>
            </div>

            <div class="comments-section">
                <h2>コメント({{ $item->comments->count() }})</h2>
                <div class="comments-list">
                    @forelse ($item->comments->sortByDesc("created_at") as $comment)
                    <div class="comment">
                        <p class="comment-author">
                            {{ $comment->user->name }}
                            <small class="text-muted">{{ $comment->created_at->format("Y/m/d H:i") }}</small>
                        </p>
                        <p class="comment-text">{{ $comment->comment }}</p>
                        @if (Auth::id() === $comment->user_id)
                        <form action="{{ route("comments.destroy", $comment) }}" method="post" onsubmit="return confirm('本当にこのコメントを削除しますか？');" class="delete-comment-form">
                            @csrf
                            @method("DELETE")
                            <button class="btn btn-danger btn-sm">削除</button>
                        </form>
                        @endif
                    </div>
                @empty
                    <p>まだコメントがありません。</p>
                @endforelse
            </div>

            <div class="add-comment-section">
                <h2>商品へのコメント</h2>
                @auth
                    <form action="{{ route("comments.store", $item) }}" method="post">
                        @csrf
                        <div class="form-group">
                            <textarea name="comment" class="form-control" rows="3" placeholder="コメントを入力してください">{{ old("comment") }}</textarea>
                            @error('comment')
                            <div class="invalid-feedback"> {{ $message }}</div>
                            @enderror
                        </div>
                        <button class="submit-comment-button" type="submit">コメントを送信する</button>
                    </form>
                @else
                    <div class="alert alert-info">
                        コメントを投稿するには<a href="{{ route('login') }}">ログイン</a>してください。
                    </div>
                @endauth
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{-- Font AwesomeのCDNを追加（アイコン表示のため） --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> {{-- jQueryを使用する場合 --}}
    <script>
        $(document).ready(function () {
            $('#like-button').on('click', function () {
                const button = $(this);
                const itemId = button.data('item-id');
                const isLoggedIn = button.data('logged-in'); // ログイン状態を取得

                // ログインしていない場合はログインページへリダイレクト
                if (!isLoggedIn) {
                    // Laravelのログインルートへリダイレクト
                    window.location.href = "{{ route('login') }}";
                    return; // ここで処理を終了
                }

                let liked = button.data('liked'); // 現在のいいね状態

                // CSRFトークンを取得
                // CSRFトークンがmetaタグに設定されていることを前提としています
                const csrfToken = $('meta[name="csrf-token"]').attr('content');

                $.ajax({
                    url: `/items/${itemId}/like`, // ルートのURLに合わせて調整
                    type: 'POST',
                    data: {
                        _token: csrfToken // CSRFトークンを送信
                    },
                    dataType: 'json', // レスポンスのデータタイプをJSONに指定
                    success: function (response) {
                        if (response.liked) {
                            // いいねされた場合
                            button.removeClass('btn-outline-secondary').addClass('btn-danger');
                            $('#like-text').text('いいね済み');
                            button.data('liked', true);
                        } else {
                            // いいねが解除された場合
                            button.removeClass('btn-danger').addClass('btn-outline-secondary');
                            $('#like-text').text('いいね');
                            button.data('liked', false);
                        }
                        // いいね数を更新
                        $('#likes-count').text(response.likes_count);

                        console.log(response.message);
                    },
                    error: function (xhr) {
                        console.error('Error:', xhr.responseText);
                        alert('いいね処理中にエラーが発生しました。');
                    }
                });
            });
        });
    </script>
@endsection