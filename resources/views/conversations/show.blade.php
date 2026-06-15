{{-- مسكن — صفحة عرض المحادثة مع مستخدم --}}
@extends('layouts.app')

@section('title', $otherUser->full_name ?? __('Chat'))

@section('content')
<div class="chat-wrap">
    <div class="chat-header">
        <a href="{{ route('messages.index') }}" class="chat-back-btn">
            <i class="fas fa-arrow-right"></i>
        </a>
        <div class="chat-header-avatar">
            @if($otherUser->profile_image)
                <img src="{{ $otherUser->profile_image }}" alt="">
            @else
                <span>{{ mb_substr($otherUser->full_name, 0, 1) }}</span>
            @endif
        </div>
        <div class="chat-header-info">
            <span class="chat-header-name">{{ $otherUser->full_name }}</span>
        </div>
    </div>

    <div class="chat-messages" id="chatMessages">
        @forelse($messages as $msg)
            @php $isMine = $msg->sender_id === auth()->id(); @endphp
            <div class="chat-row {{ $isMine ? 'chat-row-mine' : 'chat-row-other' }}">
                <div class="chat-bubble {{ $isMine ? 'chat-bubble-mine' : 'chat-bubble-other' }}">
                    <div class="chat-bubble-text">{{ $msg->message_text }}</div>
                    <div class="chat-bubble-meta">
                        <span class="chat-bubble-time">{{ $msg->created_at->format('H:i') }}</span>
                        @if($msg->edited_at)
                            <span class="chat-bubble-edited">{{ __('edited') }}</span>
                        @endif
                        @if($isMine)
                            <span class="chat-bubble-check">
                                <i class="fas fa-check"></i>
                            </span>
                        @endif
                        <div class="chat-bubble-actions">
                            @if($isMine)
                                <button class="chat-bubble-btn edit-msg-btn" title="{{ __('Edit') }}"
                                        data-msg-id="{{ $msg->id }}" data-msg-text="{{ $msg->message_text }}">
                                    <i class="fas fa-pen"></i>
                                </button>
                            @endif
                            <button type="button" class="chat-bubble-btn delete-msg-btn" title="{{ __('Delete') }}"
                                    data-msg-id="{{ $msg->id }}" data-is-mine="{{ $isMine ? '1' : '0' }}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="chat-empty">
                <i class="fas fa-comments"></i>
                <p>{{ __('No messages yet. Start chatting!') }}</p>
            </div>
        @endforelse
    </div>

    <div class="chat-input-wrap">
        <form action="{{ route('messages.store') }}" method="POST" class="chat-input-form">
            @csrf
            <input type="hidden" name="receiver_id" value="{{ $otherUser->id }}">
            <input type="text" name="message_text" class="chat-input" placeholder="{{ __('Type a message...') }}" required autofocus autocomplete="off">
            <button type="submit" class="chat-send-btn">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>
</div>

<div class="mt-3">
    {{ $messages->links() }}
</div>

{{-- Delete Message Modal --}}
<div class="modal fade" id="deleteMsgModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <form id="deleteMsgForm" method="POST">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">{{ __('Delete message') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div style="font-size:2.5rem; color:#dc3545; margin-bottom:0.5rem;">
                        <i class="fas fa-trash-alt"></i>
                    </div>
                    <p>{{ __('Are you sure you want to delete this message?') }}</p>
                    <div class="form-check mt-3 text-start" id="forEveryoneWrap">
                        <input class="form-check-input" type="checkbox" name="for_everyone" id="forEveryoneCheck" value="1">
                        <label class="form-check-label" for="forEveryoneCheck" style="color:var(--dark);">
                            {{ __('Delete for everyone') }}
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('Delete') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Message Modal --}}
<div class="modal fade" id="editMsgModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editMsgForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Edit message') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <textarea name="message_text" id="editMsgText" class="form-control" rows="3" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-maskan">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.chat-wrap {
    display: flex; flex-direction: column;
    height: calc(100vh - 180px);
    background: #e8e0d8;
    border-radius: 12px; overflow: hidden;
    border: 1px solid var(--gray-100);
}
.chat-header {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 16px;
    background: var(--blue); color: #fff;
    flex-shrink: 0;
}
.chat-back-btn {
    color: #fff; font-size: 1.1rem;
    text-decoration: none; padding: 4px; border-radius: 4px;
    transition: background 0.15s;
}
.chat-back-btn:hover { color: #fff; background: rgba(255,255,255,0.15); }
.chat-header-avatar {
    width: 40px; height: 40px; border-radius: 50%;
    background: rgba(255,255,255,0.25); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; font-weight: 700; overflow: hidden; flex-shrink: 0;
}
.chat-header-avatar img { width: 100%; height: 100%; object-fit: cover; }
.chat-header-info { flex: 1; min-width: 0; }
.chat-header-name { font-weight: 600; font-size: 0.95rem; display: block; }
.chat-messages {
    flex: 1; overflow-y: auto; padding: 16px;
    display: flex; flex-direction: column; gap: 6px;
    background: #e8e0d8;
    background-image: radial-gradient(rgba(0,0,0,0.03) 1px, transparent 1px);
    background-size: 20px 20px;
}
.chat-row { display: flex; animation: msgIn 0.2s ease-out; }
@keyframes msgIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}
.chat-row-mine { justify-content: flex-end; }
.chat-row-other { justify-content: flex-start; }
.chat-bubble {
    max-width: 75%; padding: 8px 12px;
    border-radius: 8px; position: relative;
    box-shadow: 0 1px 2px rgba(0,0,0,0.08);
    word-wrap: break-word;
}
.chat-bubble-mine { background: #d9fdd3; color: var(--dark); border-bottom-right-radius: 2px; }
.chat-bubble-other { background: #fff; color: var(--dark); border-bottom-left-radius: 2px; }
.chat-bubble-text { font-size: 0.9rem; line-height: 1.5; white-space: pre-wrap; }
.chat-bubble-meta {
    display: flex; align-items: center; gap: 4px;
    margin-top: 2px; direction: ltr;
    justify-content: flex-end;
}
.chat-bubble-time { font-size: 0.6rem; color: var(--gray-500); }
.chat-bubble-edited { font-size: 0.6rem; color: var(--gray-500); font-style: italic; }
.chat-bubble-check { font-size: 0.6rem; color: #4fc3f7; }
.chat-bubble-actions { display: none; gap: 2px; }
.chat-row:hover .chat-bubble-actions { display: flex; }
.chat-bubble-btn {
    background: none; border: none;
    color: var(--gray-500); cursor: pointer;
    font-size: 0.6rem; padding: 2px 4px; border-radius: 3px;
    transition: all 0.15s;
}
.chat-bubble-btn:hover { color: var(--blue); background: rgba(0,0,0,0.05); }
.chat-empty { margin: auto; text-align: center; color: var(--gray-500); }
.chat-empty i { font-size: 3rem; display: block; margin-bottom: 8px; color: var(--gold); }
.chat-empty p { font-size: 0.9rem; }
.chat-input-wrap {
    padding: 10px 16px; background: #f0ebe5;
    flex-shrink: 0; border-top: 1px solid rgba(0,0,0,0.05);
}
.chat-input-form { display: flex; gap: 8px; align-items: center; }
.chat-input {
    flex: 1; border: none; border-radius: 24px;
    padding: 10px 16px; font-size: 0.9rem;
    background: #fff; outline: none; color: var(--dark);
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.06);
}
.chat-input::placeholder { color: var(--gray-400); }
.chat-send-btn {
    width: 42px; height: 42px; border-radius: 50%;
    background: var(--blue); color: #fff; border: none;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 1rem; flex-shrink: 0;
    transition: all 0.2s;
}
.chat-send-btn:hover { background: #24507a; transform: scale(1.05); }
</style>
@endpush

@push('scripts')
<script>
    var chatBox = document.getElementById('chatMessages');
    if (chatBox) { chatBox.scrollTop = chatBox.scrollHeight; }
    document.querySelectorAll('.chat-input').forEach(input => {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                this.closest('form').submit();
            }
        });
    });
    document.querySelectorAll('.edit-msg-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('editMsgText').value = this.dataset.msgText;
            document.getElementById('editMsgForm').action =
                '{{ route("messages.edit", "__MSG__") }}'.replace('__MSG__', this.dataset.msgId);
            new bootstrap.Modal(document.getElementById('editMsgModal')).show();
        });
    });
    document.querySelectorAll('.delete-msg-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('deleteMsgForm').action =
                '{{ route("messages.delete", "__MSG__") }}'.replace('__MSG__', this.dataset.msgId);
            document.getElementById('forEveryoneCheck').checked = false;
            document.getElementById('forEveryoneWrap').style.display =
                this.dataset.isMine === '1' ? '' : 'none';
            new bootstrap.Modal(document.getElementById('deleteMsgModal')).show();
        });
    });
</script>
@endpush
