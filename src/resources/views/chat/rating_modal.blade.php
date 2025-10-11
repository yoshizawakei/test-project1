{{-- chat.rating_modal.blade.php (デザイン組み込み修正版) --}}

<div class="modal fade" id="ratingModal" tabindex="-1" aria-labelledby="ratingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            {{-- 【デザイン調整】モーダルのヘッダーは不要なため、閉じるボタンのみ残す --}}
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('transaction.rate.store', $transaction) }}" method="POST">
                @csrf
                <div class="modal-body text-center pt-0 px-4">
                    {{-- 【デザイン調整】タイトルを中央に配置 --}}
                    <h4 class="mb-4 fw-bold">取引が完了しました。</h4>

                    {{-- 評価対象の相手の名前を取得 --}}
                    @php
                        $ratedUser = $transaction->seller_id === Auth::id() ? $transaction->buyer : $transaction->seller;
                    @endphp

                    {{-- 質問と星評価をカードで囲む --}}
                    <div class="card p-3 mb-4" style="background-color: #f8f8f0; border: none;">
                        <p class="fw-bold mb-3">今回の取引相手（{{ $ratedUser->name }} さん）はどうでしたか？</p>

                        {{-- ⭐ 星評価セクション ⭐ --}}
                        <div class="rating-stars" id="rating-stars-container">
                            {{-- 星の数だけループしてラジオボタンを生成 (5から1の順) --}}
                            @for ($i = 5; $i >= 1; $i--)
                                <input type="radio" id="star{{ $i }}" name="score" value="{{ $i }}" class="rating-radio"
                                    required @if (old('score') == $i) checked @endif>
                                {{-- 星のラベル (CSSで星アイコンを表示) --}}
                                <label for="star{{ $i }}" title="{{ $i }}点">
                                    <i class="fas fa-star"></i>
                                </label>
                            @endfor
                        </div>
                        @error('score')
                            <div class="text-danger mt-1 small">{{ $errors->first('score') }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 text-start">
                        <label for="comment" class="form-label fw-bold">取引の感想・コメント（任意）</label>
                        <textarea class="form-control" id="comment" name="comment" rows="4"
                            placeholder="具体的なコメントがあると、相手もより参考になります。">{{ old('comment') }}</textarea>
                        @error('comment')
                            <div class="text-danger mt-1 small">{{ $errors->first('comment') }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center">
                    {{-- ボタンのスタイルをデザインに合わせる --}}
                    <button type="submit" class="btn btn-rate-submit btn-lg"
                        style="background-color: #ff6347; border-color: #ff6347;">
                        送信する
                    </button>
                    {{-- キャンセルボタンはモーダルヘッダーにあるため削除 --}}
                </div>
            </form>

            {{-- エラー表示時にモーダルを自動再表示するJavaScript --}}
            @if ($errors->any())
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        var ratingModal = new bootstrap.Modal(document.getElementById('ratingModal'));
                        ratingModal.show();
                    });
                </script>
            @endif
        </div>
    </div>
</div>

{{-- **重要:** 星のデザインとボタンの色を適用するためのCSS --}}
<style>
    /* 星のラジオボタンとラベルのスタイル調整 */
    .rating-stars {
        display: flex;
        justify-content: center;
        direction: rtl;
        /* 星の順序を逆にする */
    }

    .rating-stars input[type="radio"] {
        display: none;
        /* ラジオボタン自体は非表示 */
    }

    .rating-stars label {
        font-size: 2.5rem;
        color: #ddd;
        /* デフォルトのグレー */
        cursor: pointer;
        padding: 0 5px;
        transition: color 0.2s;
    }

    /* 星にマウスオーバーしたとき、および選択された星のスタイル */
    .rating-stars label:hover,
    .rating-stars label:hover~label,
    .rating-stars input[type="radio"]:checked~label {
        color: #ffc107;
        /* 選択時の黄色 */
    }

    /* 送信ボタンのスタイル */
    .btn-rate-submit {
        background-color: #ff6347 !important;
        border-color: #ff6347 !important;
        color: white;
        width: 150px;
        font-size: 1.1rem;
    }
</style>