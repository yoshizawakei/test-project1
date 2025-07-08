@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/items/purchase.css') }}">
@endsection

@section('content')
    <div class="container">
        <form action="{{ route("items.createCheckoutSession", ["item" => $item->id]) }}" method="post" class="flex-form" id="purchase-form">
            @csrf
            <div class="content-left">
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

                <div class="payment-method-section">
                    <h3 class="section-title">支払い方法</h3>
                    <select class="payment-select" id="payment-method-select" name="payment_method">
                        <option value="">選択してください</option>
                        <option value="convenience_store">コンビニ払い</option>
                        <option value="credit_card">カード支払い</option>
                    </select>
                    @error('payment_method')
                        <div class="error-message">
                            <strong>{{ $message }}</strong>
                        </div>
                    @enderror
                </div>
                <hr class="divider">

                <div class="delivery-address-section">
                    <div class="delivery-address-header">
                        <h3 class="section-title">配送先</h3>
                        <a href="{{ route("profile.address.edit", ["item_id" => $item->id]) }}" class="change-address-link">変更する</a>
                    </div>
                    @if (Auth::user() && Auth::user()->profile && Auth::user()->profile->postal_code && Auth::user()->profile->address)
                        <p class="address-postal-code">〒{{ substr(Auth::user()->profile->postal_code, 0, 3) }}-{{ substr(Auth::user()->profile->postal_code, 3) }}</p>
                        <p class="address-details">{{ Auth::user()->profile->address }}</p>
                        <p class="address-details">{{ Auth::user()->profile->building_name }}</p>
                        <input type="hidden" name="user_profile_exists" value="1">
                    @else
                        <p>配送先情報がありません。</p>
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
                        <span class="summary-value" id="selected-payment-method-summary">選択されていません</span>
                    </div>
                </div>
                @if (Auth::user() && Auth::user()->profile && Auth::user()->profile->postal_code && Auth::user()->profile->address)
                    <button type="submit" class="purchase-button" id="purchase-button">購入する</button>
                @else
                    <a href="{{ route("mypage.profile") }}" class="purchase-button">住所の設定へ</a>
                @endif
            </div>
        </form>
    </div>

@endsection

@section('scripts')
    <!-- <script src="https://js.stripe.com/v3/"></script> -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const paymentSelect = document.getElementById('payment-method-select');
            const paymentSummaryValue = document.getElementById('selected-payment-method-summary');
            const form = document.getElementById('purchase-form');
            const purchaseButton = document.getElementById('purchase-button');

            if (paymentSelect) {
                const updatePaymentSummary = () => {
                    const selectedOption = paymentSelect.options[paymentSelect.selectedIndex];
                    paymentSummaryValue.textContent = selectedOption.textContent === "" ? "選択されていません" : selectedOption.textContent;
                };

                updatePaymentSummary();
                paymentSelect.addEventListener("change", updatePaymentSummary);
            } else {
                console.error("payment-method-select が見つかりません。");
            }

            if (purchaseButton) {
                form.addEventListener("submit", async function(event) {
                    event.preventDefault();

                    const selectedPaymentMethod = paymentSelect.value;
                    const userProfileExists = document.querySelector("input[name='user_profile_exists']").value;

                    if (selectedPaymentMethod === "") {
                        alert("支払い方法を選択してください。");
                        purchaseButton.disabled = false;
                        return;
                    }
                    if (userProfileExists === "0") {
                        alert("配送先が設定されていません");
                        purchaseButton.disabled = false;
                        return;
                    }

                    purchaseButton.disabled = true;

                    form.submit();
                });
            } else {
                console.error("purchase-button が見つかりません。");
            }
        });
    </script>
@endsection