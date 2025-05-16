@extends('layouts.app')

@section("css")
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
    <div class="container">
        <div class="product-image">
            <img src="https://placehold.jp/300x300.png" alt="商品画像">
        </div>
        <div class="product-details">
            <h1 class="product-title">商品名がここに入る</h1>
            <p class="brand-name">ブランド名</p>
            <div class="price-info">
                <span class="price">¥47,000</span> <span class="tax">(税込)</span>
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
                    <li>カラー：<span class="detail-value">グレー</span></li>
                    <li>状態：<span class="detail-value">新品</span></li>
                    <li>詳細：<span class="detail-value">商品の状態は良好です。傷もありません。購入後、即日発送いたします。</span></li>
                </ul>
            </div>

            <div class="product-info">
                <h2>商品の情報</h2>
                <ul>
                    <li>カテゴリー：<span class="detail-value">洋服</span>, <span class="detail-value">メンズ</span></li>
                    <li>商品の状態：<span class="detail-value">良好</span></li>
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