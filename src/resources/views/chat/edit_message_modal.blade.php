{{-- chat.edit_message_modal.blade.php (最終修正版) --}}

{{-- 認証ユーザーが送信したメッセージのみ、モーダルを表示 --}}
@if ($message->user_id === Auth::id())
    <div class="modal fade" id="editModal-{{ $message->id }}" tabindex="-1"
        aria-labelledby="editModalLabel-{{ $message->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel-{{ $message->id }}">メッセージ編集</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- フォームにメッセージIDを保持するためのhiddenフィールドを追加 --}}
                <form action="{{ route('chat.message.update', $message) }}" method="POST">
                    @csrf
                    @method('PUT')
                    {{-- 【重要】エラー時に再表示するモーダルを特定するための隠しフィールド --}}
                    <input type="hidden" name="message_id" value="{{ $message->id }}">

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit-content-{{ $message->id }}" class="form-label fw-bold">メッセージ内容</label>
                            <textarea class="form-control" id="edit-content-{{ $message->id }}" name="content" rows="4"
                                required>{{ old('content', $message->content) }}</textarea>

                            {{-- 【修正点】エラーメッセージの表示を安全な方法に変更 --}}
                            @error('content')
                                <div class="text-danger mt-1 small">{{ $errors->first('content') }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                        <button type="submit" class="btn btn-primary">更新</button>
                    </div>
                </form>

                {{-- バリデーションエラー時のモーダル自動再表示 --}}
                @if ($errors->any() && old('message_id') == $message->id)
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            // Bootstrap 5 の Modal クラスを使用してモーダルを表示
                            var editModal = new bootstrap.Modal(document.getElementById('editModal-' + @json($message->id)));
                            editModal.show();
                        });
                    </script>
                @endif
            </div>
        </div>
    </div>
@endif