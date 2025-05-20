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
            <div class="review-wishlist">
                <div class="review">
                    <span class="star-icon">☆</span> <span class="review-count">0</span>
                </div>
                <div class="wishlist">
                    <span class="heart-icon">♡</span> <span class="wishlist-count">1</span>
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
                <h2>コメント(1)</h2>
                <div class="comment">
                    <p class="comment-author">admin</p>
                    <p class="comment-text">こちらにコメントが入ります。</p>
                </div>
            </div>

            <div class="add-comment-section">
                <h2>商品へのコメント</h2>
                <textarea placeholder="コメントを入力"></textarea>
                <button class="submit-comment-button">コメントを送信する</button>
            </div>
        </div>
    </div>
@endsection