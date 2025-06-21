@extends('layouts.app')

@section("css")
    <link rel="stylesheet" href="{{ asset('css/mypage/index.css') }}">
@endsection

@section('content')
    <div class="profile__container">
        <div class="profile__header">
            <div class="profile__inner">
                <div class="user-avatar">
                    @if (Auth::check() && Auth::user()->profile && Auth::user()->profile->profile_image)
                        <img src="{{ asset("storage/" . Auth::user()->profile->profile_image) }}" alt="プロフィール画像">
                    @else
                        <!-- デフォルト画像 -->
                    @endif
                </div>
                <h2 class="profile__name">
                    @if (Auth::check())
                        @if (Auth::user()->profile && Auth::user()->profile->username)
                            {{ Auth::user()->profile->username }}
                        @else
                            {{ Auth::user()->name }}
                        @endif
                    @else
                        ゲストユーザー
                    @endif
                </h2>
            </div>
            <div class="profile__info">
                @if (Auth::check())
                    <a href="{{ route("mypage.profile") }}" class="profile__edit-button">プロフィールを編集</a>
                @endif
            </div>
        </div>
    </div>
    <div class="mypage-tabs">
        <div class="mypage-tabs__container">
            <button class="mypage-tabs__button active" onclick="showMyPageTab('exhibited')">出品中の商品</button>
            <button class="mypage-tabs__button" onclick="showMyPageTab('purchased')">購入した商品</button>
        </div>
    </div>

    <!-- 出品した商品 -->
    <div id="exhibited-content" class="mypage-product-grid mypage-tab-content active-content">
        @forelse ($exhibitedItems as $item)
            <a href="{{ route("items.detail", $item->id) }}" class="mypage-product-item-link">
                <div class="mypage-product-item">
                    <div class="mypage-product-image">
                        <img src='{{ asset("$item->image_path") }}' alt="{{ $item->item_name }}">
                        @if ($item->sold_at)
                            <span class="sold-out-overlay">SOLD</span>
                        @endif
                    </div>
                    <div class="mypage-product-name">{{ $item->item_name }}</div>
                    <div class="mypage-product-price">¥{{ number_format($item->price) }}</div>
                </div>
            </a>
        @empty
            <p class="no-items-message">出品中の商品はありません。</p>
        @endforelse
    </div>

    <!-- 購入した商品 -->
    <div id="purchased-content" class="mypage-product-grid mypage-tab-content hidden">
        <p class="no-items-message">購入した商品を読み込み中...</p>
    </div>
@endsection

@section('scripts')
    <script>
        function showMyPageTab(tabId) {
            // 全てのページコンテンツを非表示にする
            const contents = document.querySelectorAll('.mypage-tab-content');
            contents.forEach(content => {
                content.classList.remove('active-content');
                content.classList.add('hidden');
            });

            // クリックされたボタンに対応するコンテンツを表示する
            const targetContent = document.getElementById(tabId + '-content');
            if (targetContent) {
                targetContent.classList.add('active-content');
                targetContent.classList.remove('hidden');
            }

            // アクティブなボタンを更新する
            const buttons = document.querySelectorAll('.mypage-tabs__button');
            buttons.forEach(button => {
                button.classList.remove('active');
            });
            event.currentTarget.classList.add('active');

            if (tabId === "purchased") {
                loadPurchasedItems();
            }
        }

        async function loadPurchasedItems() {
            const purchasedContent = document.getElementById('purchased-content');
            if (purchasedContent.innerHTML.trim() !== "<p class='no-items-message'>購入した商品を読み込み中...</p>") {
                purchasedContent.innerHTML = "<p class='no-items-message'>購入した商品を読み込み中...</p>";
            }

        try {
                const response = await fetch("/api/purchased-items", {
                    method: "GET",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        "Accept": "application/json"
                    }
                });

                if (response.status === 401) {
                    purchasedContent.innerHTML = "<p class='no-items-message'>購入した商品を表示するにはログインが必要です。</p>";
                    return;
                }

                if (!response.ok) {
                    throw new Error("HTTP error! Status: ${response.status}");
                }

                const items = await response.json();

                if (items.length === 0) {
                    purchasedContent.innerHTML = "<p class='no-items-message'>購入した商品はまだありません。</p>";
                } else {
                    purchasedContent.innerHTML = "";
                    items.forEach(item => {
                        const productItemHtml = `
                            <a href="/items/${item.id}" class="mypage-product-item-link">
                                <div class="mypage-product-item">
                                    <div class="mypage-product-image">
                                        <img src="${item.image_path.startsWith('http') ? item.image_path : '/storage/' + item.image_path}" alt="${item.item_name}">
                                        ${item.sold_out ? '<span class="sold-out-overlay">SOLD</span>' : ''}
                                    </div>
                                    <div class="mypage-product-name">${item.item_name}</div>
                                    <div class="mypage-product-price">¥${numberWithCommas(item.price)}</div>
                                </div>
                            </a>
                        `;
                        purchasedContent.insertAdjacentHTML("beforeend", productItemHtml);
                    });
                }
            } catch (error) {
                console.error("購入した商品の読み込み中にエラーが発生しました:", error);
                purchasedContent.innerHTML = "<p>購入した商品の読み込み中にエラーが発生しました。</p>";
            }
        }

        function numberWithCommas(x) {
            if (x === null || typeof x === "undefined") {
                return " ";
            }
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

    </script>
@endsection