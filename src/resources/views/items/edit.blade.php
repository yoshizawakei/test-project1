@extends('layouts.app')

@section("css")
    <link rel="stylesheet" href="{{ asset('css/items/sell.css') }}">
@endsection

@section("content")
    <div class="main-content">
        <h2 class="page-title">商品情報の編集</h2>
        @if (session("success"))
            <div class="alert alert-success">
                {{ session("success") }}
            </div>
        @endif
        @if (session("error"))
            <div class="alert alert-danger">
                {{ session("error") }}
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

        <form action="{{ route('items.update', $item->id) }}" method="post" enctype="multipart/form-data"
            class="product-form">
            @csrf
            @method('PUT')
            <section class="form-section">
                <h3 class="label-title">商品画像</h3>
                <div class="image-upload-area">
                    <input type="file" id="product-image-upload" name="image" accept="image/*" style="display: none;">
                    <label for="product-image-upload" class="upload-label">
                        <span class="upload-text">画像を選択する</span>
                        <p class="drag-drop-text">または画像をドラッグ＆ドロップ</p>
                    </label>
                    <div id="image-preview" class="image-preview">
                        @if ($item->image_path)
                            <img src="{{ asset($item->image_path) }}" alt="現在の画像" class="preview-image">
                        @endif
                    </div>
                </div>
                @error("image")
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </section>

            <section class="form-section">
                <h3 class="section-title">商品の詳細</h3>
                <div class="form-item">
                    <label for="category_ids" class="label-title">カテゴリー</label>
                    <div class="category-buttons" id="category-buttons-container">
                        @foreach ($categories as $category)
                            <button type="button"
                                class="category-button {{ in_array($category->id, old('category_ids', $item->categories->pluck('id')->toArray() ?? [])) ? 'selected' : '' }}"
                                data-category-id="{{ $category->id }}">
                                {{ $category->name }}
                            </button>
                        @endforeach
                    </div>
                    @error("category_ids")
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-item">
                    <label for="condition" class="label-title">商品の状態</label>
                    <div class="select-wrapper">
                        <select id="product-status" name="condition"
                            class="form-control @error("condition") is-invalid @enderror">
                            <option value="">選択してください</option>
                            @foreach ($conditions as $conditionOption)
                                <option value="{{ $conditionOption }}" {{ old("condition", $item->condition) == $conditionOption ? "selected" : "" }}>{{ $conditionOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error("condition")
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
            </section>

            <section class="form-section">
                <h3 class="section-title">商品名と説明</h3>
                <div class="form-item">
                    <label for="product-name" class="label-title">商品名</label>
                    <input type="text" id="product-name" name="item_name" value="{{ old("item_name", $item->item_name) }}"
                        placeholder="商品名を入力してください" required class="form-control @error('item_name') is-invalid @enderror">
                    @error("item_name")
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-item">
                    <label for="brand-name" class="label-title">ブランド名（任意）</label>
                    <div class="select-wrapper">
                        <select id="brand-name" name="brand_id"
                            class="form-control @error('brand_id') is-invalid @enderror">
                            <option value="">選択してください（任意）</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old("brand_id", $item->brand_id) == $brand->id ? "selected" : "" }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error("brand_id")
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-item">
                    <label for="product-description" class="label-title">商品の説明</label>
                    <textarea id="product-description" name="description" rows="5" placeholder="商品の説明を入力してください" required
                        class="form-control @error("description") is-invalid @enderror">{{ old("description", $item->description) }}</textarea>
                    @error("description")
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-item">
                    <label for="selling-price" class="label-title">販売価格</label>
                    <div class="form-item price-input">
                        <input type="number" id="selling-price" name="price" value="{{ old("price", $item->price) }}"
                            placeholder="¥" min="0" required class="form-control @error('price') is-invalid @enderror">
                    </div>
                    @error("price")
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
            </section>

            <div class="submit-button-container">
                <button type="submit" class="submit-button">更新する</button>
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
        const uploadText = document.querySelector('.upload-text');
        const dragDropText = document.querySelector('.drag-drop-text');

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
                if (uploadText && dragDropText) {
                    uploadText.style.display = 'block';
                    dragDropText.style.display = 'block';
                }
                return;
            }

            // アップロードテキストとドラッグ＆ドロップテキストを非表示にする
            // if (uploadText && dragDropText) {
            //     uploadText.style.display = 'none';
            //     dragDropText.style.display = 'none';
            // }

            const file = files[0];
            if (!file.type.startsWith('image/')) {
                alert('画像ファイルを選択してください。');
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.classList.add('preview-image');
                imagePreview.appendChild(img);
            };
            reader.readAsDataURL(file);
        }

        // カテゴリボタンの複数選択
        const categoryButtonsContainer = document.getElementById('category-buttons-container');
        const categoryButtons = categoryButtonsContainer.querySelectorAll('.category-button');
        const hiddenCategoryInputs = categoryButtonsContainer.querySelectorAll('.category-hidden-input');

        categoryButtons.forEach(button => {
            button.addEventListener('click', () => {
                const categoryId = button.dataset.categoryId;
                const correspondingHiddenInput = document.querySelector(`.category-hidden-input[data-category-id="${categoryId}"]`);

                if (button.classList.contains('selected')) {
                    // 既に選択されている場合、選択解除
                    button.classList.remove('selected');
                    correspondingHiddenInput.disabled = true; // 無効にして送信対象から外す
                } else {
                    // 選択されていない場合、選択
                    button.classList.add('selected');
                    correspondingHiddenInput.disabled = false; // 有効にして送信対象にする
                }
            });
        });


        const productStatusSelect = document.getElementById('product-status');
        if (productStatusSelect.value) {
            productStatusSelect.value = "{{ old('condition') }}";
        }
    </script>
@endsection