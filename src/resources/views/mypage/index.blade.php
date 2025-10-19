@extends('layouts.app')

@section("css")
    {{-- mypage/index.css はそのまま利用します --}}
    <link rel="stylesheet" href="{{ asset('css/mypage/index.css') }}">
    {{-- FontAwesomeアイコンの読み込み (星評価用) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
@endsection

@section('content')

    {{-- ⭐ 1. プロフィールセクション ⭐ --}}
    <div class="profile__container">
        <div class="profile__header">
            <div class="profile__inner">
                {{-- アバター --}}
                <div class="user-avatar">
                    @if (Auth::check() && Auth::user()->profile && Auth::user()->profile->profile_image)
                        <img src="{{ asset("storage/" . Auth::user()->profile->profile_image) }}" alt="プロフィール画像">
                    @else
                        <img src="{{ asset('img/logo.svg') }}" alt="デフォルトプロフィール画像">
                    @endif
                </div>

                <div class="user-info-wrapper">
                    {{-- ユーザー名 --}}
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

                    {{-- ⭐ 評価星表示 ⭐ --}}
                    <div class="user-rating">
                        @for ($i = 1; $i <= 5; $i++)
                            @if ($i <= floor($averageRating))
                                <i class="fas fa-star full-star"></i>
                            @elseif ($i - 0.5 <= $averageRating)
                                <i class="fas fa-star-half-alt half-star"></i>
                            @else
                                <i class="far fa-star empty-star"></i>
                            @endif
                        @endfor
                    </div>
                </div>
            </div>

            {{-- プロフィール編集ボタン --}}
            <div class="profile__info">
                @if (Auth::check())
                    <a href="{{ route("mypage.profile") }}" class="profile__edit-button">プロフィールを編集</a>
                @endif
            </div>
        </div>
    </div>

    {{-- ⭐ 2. タブとコンテンツセクション ⭐ --}}
    <div class="mypage-tabs-content-wrapper">
        <div class="mypage-tabs">
            <div class="mypage-tabs__container">
                {{-- 出品した商品タブ --}}
                <button class="mypage-tabs__button active" onclick="showMyPageTab('exhibited', event)">
                    出品した商品
                </button>

                {{-- 購入した商品タブ --}}
                <button class="mypage-tabs__button" onclick="showMyPageTab('purchased', event)">
                    購入した商品
                </button>

                {{-- 取引中の商品タブ --}}
                <button class="mypage-tabs__button" onclick="showMyPageTab('transactions', event)">
                    取引中の商品
                    <span class="badge transaction-badge">{{ $transactions->count() }}</span>
                </button>
            </div>
        </div>

        {{-- ⭐ 3. コンテンツエリア ⭐ --}}

        <div id="exhibited-content" class="mypage-product-grid mypage-tab-content active-content">
            @forelse ($exhibitedItems as $item)
                <a href="{{ route("items.detail", $item->id) }}" class="mypage-product-item-link">
                    <div class="mypage-product-item">
                        <div class="mypage-product-image">
                            <img src="{{ asset($item->image_path) }}" alt="{{ $item->item_name }}">
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

        <div id="purchased-content" class="mypage-product-grid mypage-tab-content hidden">
            <p class="no-items-message">購入した商品を読み込み中...</p>
        </div>

        <div id="transactions-content" class="mypage-product-grid mypage-tab-content hidden">
            @forelse ($transactions as $transaction)
                @php
                    $item = $transaction->item;
                    $opponent = $transaction->seller_id === Auth::id() ? $transaction->buyer : $transaction->seller;
                    // ★ 修正箇所: $unreadCount の定義を追加 ★
                    $unreadCount = $transaction->unread_count ?? 0;
                @endphp
                <div class="mypage-product-item-wrapper transaction-item-wrapper">
                    <a href="{{ route("items.detail", $item->id) }}" class="mypage-product-item-link">
                        <div class="mypage-product-item">
                            <div class="mypage-product-image">
                                <img src="{{ asset($item->image_path) }}" alt="{{ $item->item_name }}">
                            </div>
                            <div>
                                <div>
                                    <div class="mypage-product-name">{{ $item->item_name }}</div>
                                    <div class="mypage-product-price">¥{{ number_format($item->price) }}</div>
                                </div>
                                <div class="chat-button-wrapper mt-2">
                                    <a href="{{ route('chat.show', $transaction) }}"
                                        class="btn btn-sm btn-primary chat-link-button">
                                        取引チャットへ
                                        @if ($unreadCount > 0)
                                            <span class="badge unread-badge">{{ $unreadCount }}</span>
                                        @endif
                                    </a>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <p class="no-items-message">現在、取引中の商品はありません。</p>
            @endforelse
        </div>
    </div> {{-- .mypage-tabs-content-wrapper --}}
@endsection

@section('scripts')
    <script>
        // event パラメータを追加し、event.currentTarget を使用するように修正
        function showMyPageTab(tabId, event) {
            // console.log("① showMyPageTab が実行されました。タブID:", tabId);

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
            // クリックされたボタン自身に active クラスを追加
            if (event && event.currentTarget) {
                event.currentTarget.classList.add('active');
            }

            if (tabId === "purchased") {
                // console.log("② 'purchased' タブが選択されました。loadPurchasedItems() を呼び出します。");
                loadPurchasedItems();
            }

        }

        async function loadPurchasedItems() {
            const purchasedContent = document.getElementById('purchased-content');
            if (!purchasedContent) return;

            // 読み込み中メッセージを表示
            purchasedContent.innerHTML = "<p class='no-items-message'>購入した商品を読み込み中...</p>";

            const apiUrl = "{{ route('api.purchased.index') }}";

            // ログインチェック（念のため）
            if (!('{{ Auth::check() }}' === '1')) {
                purchasedContent.innerHTML = "<p class='no-items-message'>購入した商品を表示するにはログインが必要です。</p>";
                return;
            }

            try {
                const headers = {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    "Accept": "application/json"
                };

                const response = await fetch(apiUrl, {
                    method: "GET",
                    headers: headers
                });

                // 401 Unauthorized (認証失敗) の場合の処理
                if (response.status === 401) {
                    purchasedContent.innerHTML = "<p class='no-items-message'>ログインが必要です。</p>";
                    return;
                }

                if (!response.ok) {
                    // 500エラーなどの場合
                    const errorText = await response.text();
                    console.error("API Fetch Error Body:", errorText);
                    purchasedContent.innerHTML = `<p class='no-items-message'>APIエラーが発生しました。Status: ${response.status}。コンソールを確認してください。</p>`;
                    return;
                }

                const items = await response.json();

                // 取得データの中身を確認
                console.log("Fetched Items Data:", items);

                if (items.length === 0) {
                    purchasedContent.innerHTML = "<p class='no-items-message'>購入した商品はまだありません。</p>";
                } else {
                    purchasedContent.innerHTML = "";
                    // Laravelの asset('storage') の値をJS側で取得
                    const storageUrl = "{{ asset('storage') }}";

                    items.forEach(item => {
                        let chatButtonHtml = '';

                        // チャット導線のロジック
                        if (item.transaction_id) {
                            const chatUrl = `/transactions/${item.transaction_id}/chat`;

                            if (item.transaction_status && item.transaction_status !== 'completed') {
                                // 取引中の場合
                                chatButtonHtml = `
                            <div class="chat-button-wrapper mt-2">
                                <a href="${chatUrl}" class="btn btn-sm btn-primary chat-link-button">
                                    取引チャットへ進む
                                </a>
                            </div>
                        `;
                            } else {
                                // 取引完了の場合
                                chatButtonHtml = `
                            <div class="chat-button-wrapper mt-2">
                                <span class="badge bg-success transaction-status-badge">取引完了・評価済み</span>
                            </div>
                        `;
                            }
                        }

                        // 画像パスの生成: storageUrlとimage_pathを結合
                        const imageUrl = item.image_path ? `{{ asset('') }}/${item.image_path}` : '{{ asset('img/logo.svg') }}';

                        const productItemHtml = `
                                <div class="mypage-product-item-wrapper transaction-item-wrapper">
                                    <a href="/items/${item.id}" class="mypage-product-item-link">
                                        <div class="mypage-product-item">
                                            <div class="mypage-product-image">
                                                <img src="${imageUrl}" alt="${item.item_name}">
                                                ${item.sold_out ? '<span class="sold-out-overlay">SOLD</span>' : ''}
                                            </div>
                                            <div>
                                                <div>
                                                    <div class="mypage-product-name">${item.item_name}</div>
                                                    <div class="mypage-product-price">¥${numberWithCommas(item.price)}</div>
                                                </div>
                                                <div class="chat-button-wrapper mt-2">
                                                    ${chatButtonHtml}
                                                </div>
                                            </div
                                        </div>
                                    </a>
                                </div>
                            `;
                        purchasedContent.insertAdjacentHTML("beforeend", productItemHtml);
                    });
                }
            } catch (error) {
                console.error("購入した商品の読み込み中にエラーが発生しました:", error);
                purchasedContent.innerHTML = "<p>購入した商品の読み込み中にエラーが発生しました。詳細をコンソールで確認してください。</p>";
            }
        }


        function numberWithCommas(x) {
            if (x === null || typeof x === "undefined") {
                return " ";
            }
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        // 初回ロード時に 'exhibited' タブを active にする
        document.addEventListener('DOMContentLoaded', function () {
            // 特に何もしない場合、デフォルトで 'exhibited' が表示されます。
            // もしURLハッシュでタブを切り替えたい場合は、ここにロジックを追加します。
        });
    </script>
@endsection