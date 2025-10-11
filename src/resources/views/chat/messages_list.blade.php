{{-- chat/messages_list.blade.php --}}

@php
$currentUserId = Auth::id();
$defaultAvatarPath = 'img/logo.svg';
@endphp

@forelse ($messages as $message)
    @php
    $isMyMessage = $message->user_id === $currentUserId;
    $messageClass = $isMyMessage ? 'my-message' : 'their-message';

    $sender = $message->user;

    $profileImagePath = ($sender && $sender->profile && $sender->profile->profile_image)
        ? asset('storage/' . $sender->profile->profile_image)
        : asset($defaultAvatarPath);
    @endphp

    <div class="message-item {{ $messageClass }}">
        @if (!$isMyMessage)
            <div class="message-meta-left">
                <img src="{{ $profileImagePath }}" alt="{{ $sender->name }}アバター" class="profile-image-chat">
            </div>
        @endif

        <div class="message-content-wrapper">
            <div class="message-header-line">
                <div class="message-header-info">
                    <span class="user-name">{{ $sender->name }}</span>
                </div>
                <div class="message-meta-inline">
                    <img src="{{ $profileImagePath }}" alt="{{ $sender->name }}アバター" class="profile-image-chat">
                </div>
            </div>

            {{-- メッセージ --}}
            <div class="message-bubble">
                @if ($message->image_path)
                    <div class="message-image mb-2">
                        <img src="{{ asset('storage/' . $message->image_path) }}" alt="添付画像" class="message-image-content">
                    </div>
                @endif
                <p class="message-text">{!! nl2br(e($message->content)) !!}</p>
            </div>

            {{-- 編集・削除ボタン　--}}
            @if ($isMyMessage)
                <div class="message-actions">
                    <button type="button" class="btn btn-edit-message" data-message-id="{{ $message->id }}">
                        編集
                    </button>
                    <button type="button" class="btn btn-delete-message" data-message-id="{{ $message->id }}">
                        削除
                    </button>
                </div>
            @endif
        </div>
    </div>
    @if ($isMyMessage)
        @include('chat.edit_message_modal', ['message' => $message])
    @endif
@empty
    <p class="no-messages">まだメッセージはありません。</p>
@endforelse