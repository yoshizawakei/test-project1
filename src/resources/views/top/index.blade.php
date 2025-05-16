@extends('layouts.app')

@section("css")
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<div class="tag-container">
    <button class="tag-button active" onclick="showPage('recommend')">おすすめ</button>
    <button class="tag-button" onclick="showPage('mylist')">マイリスト</button>
</div>
<div class="product-grid">
    <div class="product-item">
        <div class="product-image">商品画像</div>
        <div class="product-name">商品名</div>
    </div>
    <div class="product-item">
        <div class="product-image">商品画像</div>
        <div class="product-name">商品名</div>
    </div>
    <div class="product-item">
        <div class="product-image">商品画像</div>
        <div class="product-name">商品名</div>
    </div>
    <div class="product-item">
        <div class="product-image">商品画像</div>
        <div class="product-name">商品名</div>
    </div>
    <div class="product-item">
        <div class="product-image">商品画像</div>
        <div class="product-name">商品名</div>
    </div>
    <div class="product-item">
        <div class="product-image">商品画像</div>
        <div class="product-name">商品名</div>
    </div>
    <div class="product-item">
        <div class="product-image">商品画像</div>
        <div class="product-name">商品名</div>
    </div>
</div>

@endsection