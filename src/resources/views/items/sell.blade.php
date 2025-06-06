@extends('layouts.app')

@section("css")
<link rel="stylesheet" href="{{ asset('css/sell.css') }}">
@endsection

@section("content")
<div class="main-content">
<h2 class="page-title">商品の出品</h2>

<form class="product-form">
    <section class="form-section">
        <h3 class="section-title">商品画像</h3>
        <div class="image-upload-area">
            <input type="file" id="product-image-upload" accept="image/*" multiple style="display: none;">
            <label for="product-image-upload" class="upload-label">
                <span class="upload-text">画像を選択する</span>
                <p class="drag-drop-text">または画像をドラッグ＆ドロップ</p>
            </label>
            <div id="image-preview" class="image-preview">
                </div>
        </div>
    </section>

    <section class="form-section">
        <h3 class="section-title">商品の詳細</h3>
        <div class="form-item">
            <label for="category" class="label-title">カテゴリー</label>
            <div class="category-buttons">
                <button type="button" class="category-button">ファッション</button>
                <button type="button" class="category-button">家電</button>
                <button type="button" class="category-button selected">インテリア</button>
                <button type="button" class="category-button">レディース</button>
                <button type="button" class="category-button">メンズ</button>
                <button type="button" class="category-button">コスメ</button>
                <button type="button" class="category-button">本</button>
                <button type="button" class="category-button">ゲーム</button>
                <button type="button" class="category-button">スポーツ</button>
                <button type="button" class="category-button">ポケモン</button>
                <button type="button" class="category-button">ハンドメイド</button>
                <button type="button" class="category-button">アクセサリー</button>
                <button type="button" class="category-button">おもちゃ</button>
                <button type="button" class="category-button">ベビー・キッズ</button>
            </div>
        </div>
        <div class="form-item">
            <label for="product-status" class="label-title">商品の状態</label>
            <div class="select-wrapper">
                <select id="product-status" name="product_status">
                    <option value="">選択してください</option>
                    <option value="new">新品、未使用</option>
                    <option value="used_good">目立った傷や汚れなし</option>
                    <option value="used_fair">やや傷や汚れあり</option>
                </select>
            </div>
        </div>
    </section>

    <section class="form-section">
        <h3 class="section-title">商品名と説明</h3>
        <div class="form-item">
            <label for="product-name" class="label-title">商品名</label>
            <input type="text" id="product-name" name="product_name" placeholder="商品名を入力してください">
        </div>
        <div class="form-item">
            <label for="brand-name" class="label-title">ブランド名</label>
            <input type="text" id="brand-name" name="brand_name" placeholder="ブランド名を入力してください（任意）">
        </div>
        <div class="form-item">
            <label for="product-description" class="label-title">商品の説明</label>
            <textarea id="product-description" name="product_description" rows="5" placeholder="商品の説明を入力してください"></textarea>
        </div>
    </section>

    <section class="form-section">
        <h3 class="section-title">販売価格</h3>
        <div class="form-item price-input">
            <label for="selling-price" class="label-title">¥</label>
            <input type="number" id="selling-price" name="selling_price" placeholder="0" min="0">
        </div>
    </section>

    <div class="submit-button-container">
        <button type="submit" class="submit-button">出品する</button>
    </div>
</form>
</div>
@endsection

@section("scripts")
<script>
    // 画像アップロードのJavaScript（簡易版）
    const imageUploadInput = document.getElementById('product-image-upload');
    const uploadLabel = document.querySelector('.upload-label');
    const imagePreview = document.getElementById('image-preview');

    // ドラッグ＆ドロップ処理
    uploadLabel.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadLabel.classList.add('drag-over');
    });

    uploadLabel.addEventListener('dragleave', () => {
        uploadLabel.classList.remove('drag-over');
    });

    uploadLabel.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadLabel.classList.remove('drag-over');
        handleFiles(e.dataTransfer.files);
    });

    // ファイル選択ダイアログからの処理
    imageUploadInput.addEventListener('change', (e) => {
        handleFiles(e.target.files);
    });

    function handleFiles(files) {
        imagePreview.innerHTML = ''; // 既存のプレビューをクリア

        if (files.length === 0) {
            return;
        }

        // ドラッグ＆ドロップ領域のテキストを非表示にする（画像がアップロードされたら）
        const uploadText = document.querySelector('.upload-text');
        const dragDropText = document.querySelector('.drag-drop-text');
        if (uploadText && dragDropText) {
            uploadText.style.display = 'none';
            dragDropText.style.display = 'none';
        }


        Array.from(files).forEach(file => {
            if (!file.type.startsWith('image/')) {
                return; // 画像ファイル以外はスキップ
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.classList.add('preview-image');
                imagePreview.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    }

    // カテゴリボタンの選択状態の切り替え（簡易版）
    const categoryButtons = document.querySelectorAll('.category-button');
    categoryButtons.forEach(button => {
        button.addEventListener('click', () => {
            // すでに選択されているボタンがあればselectedクラスを削除
            const currentSelected = document.querySelector('.category-button.selected');
            if (currentSelected) {
                currentSelected.classList.remove('selected');
            }
            // クリックされたボタンにselectedクラスを追加
            button.classList.add('selected');
        });
    });
</script>
@endsection