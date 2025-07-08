@extends('layouts.app')

@section("css")
<link rel="stylesheet" href="{{ asset('css/items/detail.css') }}">
@endsection

@section('content')
    <div class="container">
        <div class="product-image">
            <img src="{{ asset($item->image_path) }}" alt="{{ $item->item_name }}">
            @if ($item->sold_at)
                <div class="sold-out-overlay">SOLD</div>
            @endif
        </div>
        <div class="product-details">
            <h1 class="product-title">{{ $item["item_name"] }}</h1>
            <p class="brand-name">ブランド名：{{ $item->brand->name ?? "N/A" }}</p>
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
                        <i id="like-icon" class="fa-heart {{ Auth::check() && Auth::user()->isLiking($item) ? 'fas' : 'far' }}"></i>
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
            @if (Auth::check() && Auth::id() === $item->user_id)
                @if ($item->sold_at)
                    <p class="sold-out-message">この商品は売却済みです。</p>
                @else
                    <a href="{{ route("items.edit", $item->id) }}" class="edit-item-button">商品を編集する</a>
                @endif
            @else
                @if (!$item->sold_at)
                    <a href="{{ route("items.purchase", $item->id) }}" class="purchase-button">購入手続きへ</a>
                @else
                    <p class="sold-out-message">SOLD OUT</p>
                @endif
            @endif

            <div class="product-description">
                <h2>商品説明</h2>
                <div class="detail-value">
                    {{ $item["description"] }}
                </div>
            </div>

            <div class="product-info">
                <h2>商品の情報</h2>
                <ul>
                    <li>カテゴリー：
                        <span class="detail-value">
                            @forelse ($item->categories as $category)
                                {{ $category->name }}{{ !$loop->last ? ' , ' : '' }}
                            @empty
                                カテゴリーなし
                            @endforelse
                        </span>
                    </li>
                    <li>商品の状態：<span class="detail-value">{{ $item["condition"] }}</span></li>
                </ul>
            </div>

            <div class="comments-section">
                <h2>コメント({{ $item->comments->count() }})</h2>
                <div class="comments-list">
                    @forelse ($item->comments->sortByDesc("created_at") as $comment)
                        <div class="comment">
                            <div class="comment-header">
                                <div class="comment-avatar">
                                    @if ($comment->user->profile && $comment->user->profile->profile_image)
                                        <img src="{{ asset('storage/' . $comment->user->profile->profile_image) }}" alt="{{ $comment->user->profile->username ?? $comment->user->name }}のプロフィール画像">
                                    @else
                                        <img src="{{ asset('img/logo.svg') }}" alt="デフォルトプロフィール画像">
                                    @endif
                                </div>
                                <p class="comment-author">
                                    {{ $comment->user->profile->username ?? $comment->user->name }}
                                    <small class="text-muted">{{ $comment->created_at->format("Y/m/d H:i") }}</small>
                                </p>
                            </div>
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
                    <form action="{{ route("comments.store", $item) }}" method="post" novalidate>
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
                const isLoggedIn = button.data('logged-in');

                if (!isLoggedIn) {
                    window.location.href = "{{ route('login') }}";
                    return;
                }

                let liked = button.data("liked");
                const csrfToken = $('meta[name="csrf-token"]').attr('content');
                const likeIcon = $('#like-icon');
                const likesCountSpan = $('#likes-count'); // ここで要素を正しく取得しています

                $.ajax({
                    url: `/items/${itemId}/like`,
                    type: 'POST',
                    data: {
                        _token: csrfToken
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.liked) {
                            button.removeClass('btn-outline-secondary').addClass('btn-danger');
                            likeIcon.removeClass('far').addClass('fas');
                            button.data('liked', true);
                        } else {
                            button.removeClass('btn-danger').addClass('btn-outline-secondary');
                            likeIcon.removeClass('fas').addClass('far');
                            button.data('liked', false);
                        }
                        // ここで likesCountSpan 変数を使ってテキストを更新します。
                        likesCountSpan.text(response.likes_count);

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