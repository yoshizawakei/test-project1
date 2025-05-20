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
        @foreach ($items as $item)
        <a href="{{ route("items.detail", $item->id) }}" class="product-item-link">
            <div class="product-item">
                <div class="product-image">
                    <img src='{{ asset("$item->image_path") }}' alt="商品画像">
                </div>
                <div class="product-name">{{ $item->item_name }}</div>
                <div class="product-price">¥{{ number_format($item->price) }}</div>
            </div>
        </a>
        @endforeach
    </div>
@endsection