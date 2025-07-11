@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/mypage/profile.css') }}">
@endsection

@section('content')
    <div class="profile-container">
        <h2 class="profile-title">プロフィール設定</h2>
        <form action="{{ route("profile.edit") }}" method="POST" class="profile-form" enctype="multipart/form-data" novalidate>
            @csrf
            <div class="profile-image-section">
                <label for="profile_image_upload" class="profile-image-placeholder">
                    @if (Auth::check() && Auth::user()->profile && Auth::user()->profile->profile_image)
                        <img id="currentProfileImage" src="{{ asset('storage/' . Auth::user()->profile->profile_image) }}" alt="プロフィール画像">
                    @else
                        <img id="currentProfileImage" src="{{ asset('img/logo.svg') }}" alt="デフォルトプロフィール画像">
                    @endif
                </label>
                <input type="file" id="profile_image_upload" name="profile_image" accept="image/*" style="display: none;">
                <button type="button" class="profile-image-edit-button" onclick="document.getElementById('profile_image_upload').click()">
                    画像を編集する
                </button>
                @error('profile_image')
                    <div class="error-message">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            <div class="form-group">
                <label for="username" class="form-label">ユーザー名</label>
                <input type="text" id="username" name="username" class="form-input" placeholder="ユーザー名を入力してください" value="{{ old("username", $profile->username ?? '') }}">
                @error('username')
                    <div class="error-message">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group">
                <label for="postcode" class="form-label">郵便番号</label>
                <input type="text" id="postcode" name="postal_code" class="form-input" placeholder="例: 123-4567" value="{{ old("postal_code", isset($profile->postal_code) ? substr($profile->postal_code, 0, 3) . '-' . substr($profile->postal_code, 3) : '') }}">
                @error('postal_code')
                    <div class="error-message">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group">
                <label for="address" class="form-label">住所</label>
                <input type="text" id="address" name="address" class="form-input" placeholder="例: 東京都渋谷区" value="{{ old("address", $profile->address ?? '') }}">
                @error('address')
                    <div class="error-message">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group">
                <label for="building_name" class="form-label">建物名</label>
                <input type="text" id="building_name" name="building_name" class="form-input"
                    placeholder="例: 〇〇マンション101号室" value="{{ old("building_name", $profile->building_name ?? '') }}">
                @error('building_name')
                    <div class="error-message">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-submit-area">
                <button type="submit" class="update-button">
                    更新する
                </button>
            </div>
        </form>
    </div>
@endsection

@section("scripts")
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const profileImageUpload = document.getElementById('profile_image_upload');
            const currentProfileImage = document.getElementById('currentProfileImage');

            if (profileImageUpload && currentProfileImage) {
                profileImageUpload.addEventListener('change', function (event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            currentProfileImage.src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    </script>
@endsection