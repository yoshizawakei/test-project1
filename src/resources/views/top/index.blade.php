@extends('layouts.app')

@section("css")
<link rel="stylesheet" href="{{ asset('css/top/index.css') }}">
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
            @if (Auth::check() && Auth::user()->user_id === $item->user_id)
                @continue
            @endif
            <a href="{{ route("items.detail", $item->id) }}" class="product-item-link">
                <div class="product-item">
                    <div class="product-image">
                        <img src="{{ asset($item->image_path) }}" alt="{{ $item->item_name }}">
                        @if ($item->sold_at)
                            <div class="sold-out-overlay">
                                SOLD
                            </div>
                        @endif
                    </div>
                    <div class="product-name">{{ $item->item_name }}</div>
                    <div class="product-price">¥{{ number_format($item->price) }}</div>
                </div>
            </a>
        @endforeach
    </div>

    <!-- マイリスト -->
    <div id="mylist-content" class="product-grid page-content hidden">
        <p class="no-items-message">お気に入りした商品を読み込み中...</p>
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

        if (pageId === "mylist") {
            loadMylistItems();
        }
    }

    async function loadMylistItems() {
        const mylistContent = document.getElementById('mylist-content');
        if (mylistContent.innerHTML.trim() !== '<p class="no-items-message">お気に入りした商品を読み込み中...</p>') {
            mylistContent.innerHTML = '<p>マイリストを再読み込み中...</p>';
        }

    try {
            const response = await fetch("/api/mylist", {
                method: "GET",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    "Accept": "application/json"
                }
            });

            if (response.status === 401) {
                // mylistContent.innerHTML = "<p>マイリストを表示するにはログインが必要です。</p>";
                window.location.href = "{{ route("login") }}";
                return;
            }

            if (!response.ok) {
                throw new Error("HTTP error! Status: ${response.status}");
            }

            const items = await response.json();

            if (items.length === 0) {
                mylistContent.innerHTML = "<p>マイリストはまだ登録されていません。</p>";
            } else {
                mylistContent.innerHTML = "";
                items.forEach(item => {
                    const productItemHtml = `
                        <a href="/items/${item.id}" class="product-item-link">
                            <div class="product-item">
                                <div class="product-image">
                                    <img src="${item.image_path}" alt="${item.item_name}">
                                    ${item.sold_at ? '<div class="sold-out-overlay">SOLD</div>' : ''}
                                </div>
                                <div class="product-name">${item.item_name}</div>
                                <div class="product-price">¥${numberWithCommas(item.price)}</div>
                            </div>
                        </a>
                    `;
                    mylistContent.insertAdjacentHTML("beforeend", productItemHtml);
                });
            }
        } catch (error) {
            console.error("マイリストの読み込み中にエラーが発生しました:", error);
            mylistContent.innerHTML = "<p>マイリストの読み込み中にエラーが発生しました。</p>";
        }
    }

    function numberWithCommas(x) {
        if (x === null || typeof x === "undefined") {
            return " ";
        }
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    // document.addEventListener("DOMContentLoaded", () => {

    // });
</script>
@endsection