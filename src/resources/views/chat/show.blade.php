@extends('layouts.app')

@section("css")
    <link rel="stylesheet" href="{{ asset('css/chat/chat.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
@endsection

@section('content')
        @php
$opponent = $transaction->seller_id === Auth::id() ? $transaction->buyer : $transaction->seller;
$item = $transaction->item;
$hasRated = $transaction->ratings->where('rater_id', Auth::id())->isNotEmpty();
        @endphp

        <div class="transaction-screen-wrapper">

            {{-- 左側サイドバー --}}
            <div class="sidebar-area">
                <p class="sidebar-title">その他の取引</p>
                <div class="other-transactions-list">
                    @forelse ($transactions as $sidebarTransaction)
                        @if ($sidebarTransaction->item)
                            <a href="{{ route('chat.show', ['transaction' => $sidebarTransaction->id]) }}"
                                class="transaction-link {{ $sidebarTransaction->id == $transaction->id ? 'active' : '' }}">
                                <div class="transaction-item-name">
                                    {{ $sidebarTransaction->item->item_name }}
                                </div>
                            </a>
                        @endif
                    @empty
                        <p class="no-transactions-message">取引中の商品はありません</p>
                    @endforelse
                </div>
            </div>

            {{-- 右側メインコンテンツ --}}
            <div class="main-content-area">
                <div class="transaction-header-info">
                    <h1 class="screen-title">
                        「{{ $opponent->name }}」さんとの取引画面
                    </h1>
                    @if ($transaction->status !== 'completed' && !$hasRated)
                        <button type="button" class="btn btn-complete-transaction" data-bs-toggle="modal"
                            data-bs-target="#ratingModal">
                            取引を完了する
                        </button>
                    @else
                        <button type="button" class="btn btn-completed-status" disabled>
                            取引完了
                        </button>
                    @endif
                </div>

                {{-- 取引中の商品 --}}
                <div class="item-info-section">
                    <div class="item-image-box">
                        @if ($item && $item->image_path)
                            <img src="{{ asset($item->image_path) }}" alt="商品画像" class="item-image">
                        @else
                            <div class="placeholder-image">画像なし</div>
                        @endif
                    </div>
                    <div class="item-details">
                        <h2 class="item-name">{{ $item->item_name ?? '商品情報なし' }}</h2>
                        <p class="item-price">¥{{ number_format($item->price ?? 0) }}</p>
                    </div>
                </div>

                {{-- チャットメッセージと入力フォーム --}}
                <div class="chat-area">
                    <div id="messages-container" class="chat-messages">
                        @include('chat.messages_list', ['messages' => $transaction->messages])
                    </div>

                    @if ($transaction->status !== 'completed')
                        <div class="chat-input-area-section">
                            <form action="{{ route('chat.message.store', $transaction) }}" method="POST"
                                enctype="multipart/form-data" class="message-form">
                                @csrf
                                <div class="message-input-group">
                                    <textarea class="message-input" id="content" name="content" rows="1"
                                        placeholder="取引メッセージを記入してください" required
                                        oninput="autoExpand(this)">{{ old('content') }}</textarea>
                                    <div class="message-send-tools">
                                        <input type="file" id="image_upload" name="image" accept="image/*" class="hidden-file-input"
                                            onchange="displayFileName(this)">
                                        <label for="image_upload" class="btn btn-add-image">
                                            画像を追加
                                        </label>
                                        <span id="file-name-display" class="file-name-display">ファイルが選択されていません</span>
                                        <button type="submit" class="btn btn-send-message">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="chat-input-area-section completed-status-message">
                            この取引は完了しています。
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 取引評価モーダル --}}
        @include('chat.rating_modal', ['transaction' => $transaction, 'opponent' => $opponent])

@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // CSRFトークンを全てのAjaxリクエストに含める設定
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // メッセージ入力欄の自動拡張
        function autoExpand(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = (textarea.scrollHeight) + 'px';
        }

        // ファイル名表示
        function displayFileName(input) {
            const displayElement = document.getElementById('file-name-display');
            if (input.files && input.files[0]) {
                const fileName = input.files[0].name;
                // 画面上にファイル名を表示
                displayElement.textContent = `ファイル: ${fileName}`;
                // コンソールにもログ出力
                console.log(`画像ファイル: ${fileName} が選択されました。`);
            } else {
                // ファイルがクリアされた場合
                displayElement.textContent = 'ファイルが選択されていません';
                console.log('選択された画像ファイルがクリアされました。');
            }
        }

        $(document).ready(function () {
            const container = $('#messages-container');

            // 初回ロード時に一番下にスクロール
            setTimeout(() => {
                container.scrollTop(container[0].scrollHeight);
            }, 100);

            // 入力欄初期化
            autoExpand(document.getElementById('content'));

            // メッセージ編集モーダルの表示
            $(document).on('click', '.btn-edit-message', function (e) {
                e.preventDefault();
                const messageId = $(this).data('message-id');
                const messageItem = $(this).closest('.message-item');
                const currentTextHtml = messageItem.find('.message-bubble p.message-text').html();
                const currentText = currentTextHtml.replace(/<br\s*\/?>/gi, '\n').trim();
                const modalElement = document.getElementById(`editModal-${messageId}`);
                if (modalElement) {
                    $(modalElement).find('textarea[name="content"]').val(currentText);
                    new bootstrap.Modal(modalElement).show();
                } else {
                    console.error(`Modal with ID #editModal-${messageId} not found.`);
                }
            });

            // メッセージ削除機能
            $(document).on('click', '.btn-delete-message', function (e) {
                e.preventDefault();
                const messageId = $(this).data('message-id');
                const messageItem = $(this).closest('.message-item');
                const deleteUrl = `/messages/${messageId}`;

                if (confirm('このメッセージを完全に削除しますか？')) {
                    $.ajax({
                        url: deleteUrl,
                        type: 'DELETE',
                        dataType: 'json',
                        success: function (response) {
                            messageItem.fadeOut(300, function () { $(this).remove(); });
                        },
                        error: function (xhr) {
                            const error = xhr.responseJSON ? xhr.responseJSON.error : '不明なエラー';
                            alert(`メッセージの削除に失敗しました: ${error}`);
                            console.error('Delete Error:', xhr.responseText);
                        }
                    });
                }
            });
        });
    </script>
@endsection