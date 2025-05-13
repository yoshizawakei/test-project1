@extends('layouts.app')

@section("css")
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')

<div class="title">
    <div class="title">おすすめ</div>
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