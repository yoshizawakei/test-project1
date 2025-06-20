@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/items/purchase.css') }}">
@endsection

@section('content')
    <div class="container">
        <div class="content-left">
            <div class="product-info">
                <div class="product-image-container">
                    @if ($item->image_path)
                        <img src="{{ asset($item->image_path) }}" alt="{{ $item['item_name'] }}"
                            class="product-image-thumbnail">
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
                <select class="payment-select" id="payment-method-select">
                    <option value="">選択してください</option>
                    <option value="convenience_store">コンビニ払い</option>
                    <option value="credit_card">カード支払い</option>
                </select>
            </div>
            <hr class="divider">

            <div class="delivery-address-section">
                <div class="delivery-address-header">
                    <h3 class="section-title">配送先</h3>
                    <a href="{{ route("profile.address.edit", ["item_id" => $item->id]) }}" class="change-address-link">変更する</a>
                </div>
                @if (Auth::user() && Auth::user()->profile)
                    <p class="address-postal-code">〒
                        {{ substr(Auth::user()->profile->postal_code, 0, 3) }}-{{ substr(Auth::user()->profile->postal_code, 3) }}</p>
                    <p class="address-details">{{ Auth::user()->profile->address }}</p>
                    <p class="address-details">{{ Auth::user()->profile->building_name }}</p>
                @else
                    <p>配送先情報がありません。</p>
                @endif
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
            <button class="purchase-button">購入する</button>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const paymentSelect = document.getElementById('payment-method-select');
            const paymentSummaryValue = document.getElementById('selected-payment-method-summary');

            if (paymentSelect) {
                if (paymentSelect.value === "") { // "選択してください" が選択されている場合
                    paymentSummaryValue.textContent = '選択されていません';
                } else {
                    paymentSummaryValue.textContent = paymentSelect.options[paymentSelect.selectedIndex].textContent;
                }

                paymentSelect.addEventListener('change', function () {
                    if (this.value === "") { // "選択してください" が選択された場合
                        paymentSummaryValue.textContent = '選択されていません';
                    } else {
                        paymentSummaryValue.textContent = this.options[this.selectedIndex].textContent;
                    }
                });
            } else {
                console.error("payment-method-select が見つかりません。");
            }
        });
    </script>
@endsection