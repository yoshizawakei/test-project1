@extends('layouts.app')

@section("css")
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
    <div class="tag">
        <div class="tag-container">
            <button class="tag-button active" onclick="showPage('recommend')">おすすめ</button>
            <button class="tag-button" onclick="showPage('mylist')">マイリスト</button>
        </div>
    </div>

    <!-- おすすめ商品 -->
    <div id="recommend-content" class="product-grid page-content active-content">
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

    <!-- マイリスト -->
    <div id="mylist-content" class="product-grid page-content hidden">
        <!-- ここにマイリストの商品データを表示するロジックが入ります -->
        
        <p>マイリストはまだ登録されていません。</p>
    </div>
@endsection

@section('scripts')
<script>
    function showPage(pageId) {
        // 全てのページコンテンツを非表示にする
        const contents = document.querySelectorAll('.page-content');
        contents.forEach(content => {
            content.classList.remove('active-content');
            content.classList.add('hidden');
        });

        // クリックされたボタンに対応するコンテンツを表示する
        const targetContent = document.getElementById(pageId + '-content');
        if (targetContent) {
            targetContent.classList.add('active-content');
            targetContent.classList.remove('hidden');
        }

        // アクティブなボタンを更新する
        const buttons = document.querySelectorAll('.tag-button');
        buttons.forEach(button => {
            button.classList.remove('active');
        });
        event.currentTarget.classList.add('active');
    }
</script>
@endsection