@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/mypage/profile.css') }}">
@endsection

@section('content')
    <div class="profile-container">
        <h2 class="profile-title">プロフィール設定</h2>
        <div class="profile-image-section">
            <input type="img" class="profile-image-placeholder" name="profile_image"
                src="{{ asset('img/') }}" alt="プロフィール画像">
            <button type="button" class="profile-image-edit-button">
                画像を編集する
            </button>
        </div>
        <form action="/profile/edit" method="POST" class="profile-form">
            @csrf
            <div class="form-group">
                <label for="username" class="form-label">ユーザー名</label>
                <input type="text" id="username" name="username" class="form-input" placeholder="ユーザー名を入力してください">
            </div>

            <div class="form-group">
                <label for="postcode" class="form-label">郵便番号</label>
                <input type="text" id="postcode" name="postal_code" class="form-input" placeholder="例: 123-4567">
            </div>

            <div class="form-group">
                <label for="address" class="form-label">住所</label>
                <input type="text" id="address" name="address" class="form-input" placeholder="例: 東京都渋谷区">
            </div>

            <div class="form-group">
                <label for="building_name" class="form-label">建物名</label>
                <input type="text" id="building_name" name="building_name" class="form-input"
                    placeholder="例: 〇〇マンション101号室">
            </div>

            <div class="form-submit-area">
                <button type="submit" class="update-button">
                    更新する
                </button>
            </div>
        </form>
    </div>
@endsection