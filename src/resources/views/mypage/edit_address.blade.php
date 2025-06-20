@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/mypage/edit_address.css') }}">
@endsection

@section('content')
    <div class="edit-address-container">
        <h2 class="page-title">住所の変更</h2>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('profile.address.update') }}" method="POST" class="address-form">
            @csrf
            @method('PUT')
            <!-- 隠しでアイテムIDを送信 -->
            @if (isset($item_id))
                <input type="hidden" name="item_id" value="{{ $item_id }}">
            @endif

            {{-- 郵便番号 --}}
            <div class="form-group">
                <label for="postal_code" class="form-label">郵便番号</label>
                <input type="text" id="postal_code" name="postal_code" class="form-input"
                    value="{{ old('postal_code', preg_replace('/(\d{3})(\d{4})/', '$1-$2', $profile->postal_code ?? '')) }}"
                    placeholder="例: 123-4567">
                @error('postal_code')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            {{-- 住所 --}}
            <div class="form-group">
                <label for="address" class="form-label">住所</label>
                <input type="text" id="address" name="address" class="form-input"
                    value="{{ old('address', $profile->address ?? '') }}" placeholder="例: 東京都渋谷区神南1-1-1">
                @error('address')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            {{-- 建物名 --}}
            <div class="form-group">
                <label for="building_name" class="form-label">建物名</label>
                <input type="text" id="building_name" name="building_name" class="form-input"
                    value="{{ old('building_name', $profile->building_name ?? '') }}" placeholder="例: Coachtechビル">
                @error('building_name')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="submit-button">更新する</button>
        </form>
    </div>
@endsection