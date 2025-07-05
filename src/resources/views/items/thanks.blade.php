@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/items/thanks.css') }}">
@endsection

@section('content')
    <div class="main-content">
        <h2 class="page-title">購入完了</h2>
        <div class="success-message">
            <p>商品の購入が完了しました！</p>
            <p>コンビニ払いを選択された方には、別途案内メールをお送りいたします。</p>
            <p>ご利用ありがとうございました。</p>
            <div class="button-group">
                <a href="{{ route('top.index') }}" class="btn btn-primary">トップページに戻る</a>
                {{-- 必要であれば、購入履歴ページへのリンクなども追加できます --}}
            </div>
        </div>
    </div>
@endsection