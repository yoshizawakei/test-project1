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
                        <img src="#" alt="画像なし" class="product-image-thumbnail">
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
                    <a href="#" class="change-address-link">変更する</a>
                </div>
                @if (Auth::user() && Auth::user()->profile)
                    <p class="address-postal-code">〒 {{ Auth::user()->profile->postal_code }}</p>
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

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const paymentSelect = document.getElementById('payment-method-select');
            const paymentSummaryValue = document.getElementById('selected-payment-method-summary');

            if (paymentSelect) {
                paymentSelect.addEventListener('change', function () {
                    paymentSummaryValue.textContent = this.options[this.selectedIndex].textContent;
                });

                if (paymentSelect.options.length > 0 && paymentSelect.selectedIndex !== -1) {
                    paymentSummaryValue.textContent = paymentSelect.options[paymentSelect.selectedIndex].textContent;
                } else {
                    paymentSummaryValue.textContent = '選択されていません';
                }
            } else {
                console.error("payment-method-select が見つかりません。");
            }
        });
    </script>
@endsection