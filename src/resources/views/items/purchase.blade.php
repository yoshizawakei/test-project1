@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/items/purchase.css') }}">
@endsection

@section('content')
    <div class="container">
        <form action="{{ route("items.createCheckoutSession", ["item" => $item->id]) }}" method="post" class="flex-form" id="purchase-form">
            @csrf
            <div class="content-left">
                {{-- 商品情報セクション --}}
                <div class="product-info">
                    <div class="product-image-container">
                        @if ($item->image_path)
                            <img src="{{ asset($item->image_path) }}" alt="{{ $item->item_name }}" class="product-image-thumbnail">
                        @else
                            <img src="{{ asset("images/no-image.png") }}" alt="画像なし" class="product-image-thumbnail">
                        @endif
                    </div>
                    <div class="product-details">
                        <h2 class="product-name">{{ $item["item_name"] }}</h2>
                        <div class="product-price">
                            <span class="price">¥{{ number_format($item["price"]) }}</span> <span class="tax">(税込)</span>
                        </div>
                    </div>
                </div>
                <hr class="divider">

                {{-- 支払い方法セクション --}}
                <div class="payment-method-section">
                    <h3 class="section-title">支払い方法</h3>
                    {{-- 【修正点1】old()関数を使って、エラー時に選択肢を保持する --}}
                    <select class="payment-select" id="payment-method-select" name="payment_method">
                        <option value="">選択してください</option>
                        <option value="convenience_store" {{ old('payment_method') == 'convenience_store' ? 'selected' : '' }}>コンビニ払い</option>
                        <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>カード支払い</option>
                    </select>
                    @error('payment_method')
                        <div class="error-message">
                            <strong>{{ $message }}</strong>
                        </div>
                    @enderror
                </div>
                <hr class="divider">

                {{-- 配送先セクション --}}
                <div class="delivery-address-section">
                    <div class="delivery-address-header">
                        <h3 class="section-title">配送先</h3>
                        {{-- 【修正点2】リンク先をプロフィール編集に統一 (住所変更ページが存在しない前提) --}}
                        <a href="{{ route("mypage.profile") }}#address-section" class="change-address-link">変更する</a>
                    </div>
                    @if (Auth::user() && Auth::user()->profile && Auth::user()->profile->postal_code && Auth::user()->profile->address)
                        <p class="address-postal-code">〒{{ substr(Auth::user()->profile->postal_code, 0, 3) }}-{{ substr(Auth::user()->profile->postal_code, 3) }}</p>
                        <p class="address-details">{{ Auth::user()->profile->address }}</p>
                        <p class="address-details">{{ Auth::user()->profile->building_name }}</p>
                        <input type="hidden" name="user_profile_exists" value="1">
                    @else
                        {{-- 【修正点3】エラーメッセージの強調 --}}
                        <p class="error-message">配送先情報が設定されていません。</p>
                        <input type="hidden" name="user_profile_exists" value="0">
                    @endif
                    @error('user_profile_exists')
                    <div class="error-message">
                        <strong>{{ $message }}</strong>
                    </div>
                    @enderror
                </div>
                <hr class="divider">
            </div>

            <div class="content-right">
                <div class="order-summary">
                    <div class="summary-item">
                        <span class="summary-label">商品代金</span>
                        <span class="summary-value">
                            <span class="price-display">
                                ¥{{ number_format($item["price"]) }}<span class="tax">(税込)</span>
                            </span>
                        </span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">支払い方法</span>
                        <span class="summary-value" id="selected-payment-method-summary">
                            {{-- JSで初期値を設定するため、ここでは空のまま --}}
                        </span>
                    </div>
                </div>

                {{-- 【修正点4】ボタンの出し分けロジックを簡素化し、IDを明確化 --}}
                @php
                    $hasAddress = (Auth::user() && Auth::user()->profile && Auth::user()->profile->postal_code && Auth::user()->profile->address);
                @endphp

                @if ($hasAddress)
                    {{-- 住所が設定されている場合: 決済に進むボタン --}}
                    <button type="submit" class="purchase-button" id="purchase-submit-button">購入を確定する</button>
                @else
                    {{-- 住所が未設定の場合: 住所設定へ誘導するボタン --}}
                    <a href="{{ route("mypage.profile") }}#address-section" class="purchase-button disabled-button">住所を設定してください</a>
                @endif
            </div>
        </form>
    </div>

@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const paymentSelect = document.getElementById('payment-method-select');
            const paymentSummaryValue = document.getElementById('selected-payment-method-summary');
            const form = document.getElementById('purchase-form');
            const purchaseSubmitButton = document.getElementById('purchase-submit-button'); // ID変更

            // 1. 支払いサマリーの動的更新
            if (paymentSelect) {
                const updatePaymentSummary = () => {
                    const selectedOption = paymentSelect.options[paymentSelect.selectedIndex];
                    paymentSummaryValue.textContent = selectedOption.textContent === "" ? "選択されていません" : selectedOption.textContent;
                };

                updatePaymentSummary(); // 初回ロード時 (old()の値があれば反映)
                paymentSelect.addEventListener("change", updatePaymentSummary);
            }

            // 2. フォーム送信時の処理 (重複送信防止と最終バリデーション)
            if (purchaseSubmitButton) {
                form.addEventListener("submit", function(event) {
                    const selectedPaymentMethod = paymentSelect.value;
                    const userProfileExists = document.querySelector("input[name='user_profile_exists']").value;

                    // 支払い方法のクライアント側バリデーション
                    if (selectedPaymentMethod === "") {
                        event.preventDefault(); 
                        alert("支払い方法を選択してください。");
                        return;
                    }
                    
                    // 住所有無のチェック（念のため）
                    if (userProfileExists === "0") {
                        event.preventDefault(); 
                        // 既に住所設定へのリンクが表示されているため、アラートは不要な場合もあるが、念のため残す
                        alert("配送先が設定されていません。住所を設定してから再度お試しください。"); 
                        return;
                    }

                    // バリデーション成功後、ボタンを無効化して重複送信を防止
                    purchaseSubmitButton.disabled = true;
                    // form.submit() は event.preventDefault() が呼ばれなければ自動で実行される
                });
            }
        });
    </script>
@endsection