{{-- مسكن — صفحة عرض قائمة المحادثات --}}
@extends('layouts.app')

@section('title', __('Messages - Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ __('Messages') }}</h1>
</div>

<div class="maskan-card p-0" style="overflow:hidden;">
    @forelse($conversations as $conv)
        @php
            $userId = auth()->id();
            $lastMsg = \App\Models\Message::where(function ($q) use ($conv, $userId) {
                $q->where('sender_id', $userId)->where('receiver_id', $conv->id);
            })->orWhere(function ($q) use ($conv, $userId) {
                $q->where('sender_id', $conv->id)->where('receiver_id', $userId);
            })->where(function ($q) use ($userId) {
                $q->whereNull('deleted_for')
                  ->orWhereRaw('JSON_CONTAINS(deleted_for, ?) = 0', [json_encode($userId)]);
            })->latest()->first();
        @endphp
        <div class="conv-item">
            <a href="{{ route('messages.show', $conv) }}" class="conv-link">
                <div class="conv-avatar">
                    @if($conv->profile_image)
                        <img src="{{ $conv->profile_image }}" alt="">
                    @else
                        <span>{{ mb_substr($conv->full_name, 0, 1) }}</span>
                    @endif
                </div>
                <div class="conv-body">
                    <div class="conv-top">
                        <span class="conv-name">{{ $conv->full_name }}</span>
                        @if($lastMsg)
                            <span class="conv-time">{{ $lastMsg->created_at->diffForHumans() }}</span>
                        @endif
                    </div>
                    <div class="conv-bottom">
                        <span class="conv-preview">
                            @if($lastMsg)
                                @if($lastMsg->sender_id === auth()->id())
                                    <small class="text-muted ms-1">{{ __('You:') }}</small>
                                @endif
                                {{ Str::limit($lastMsg->message_text, 50) }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </span>
                    </div>
                </div>
            </a>
            <form action="{{ route('messages.deleteConversation', $conv) }}" method="POST" class="conv-delete-form" onsubmit="return confirm('{{ __('Delete this conversation?') }}')">
                @csrf
                <button type="submit" class="conv-delete-btn" title="{{ __('Delete conversation') }}">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </form>
        </div>
    @empty
        <div class="text-center text-muted py-5">
            <i class="fas fa-envelope-open-text fa-3x mb-3 d-block" style="color:var(--gold);"></i>
            <p>{{ __('No conversations yet') }}</p>
        </div>
    @endforelse
</div>
@endsection

@push('scripts')
<script>
    window.addEventListener('pageshow', function(e) {
        if (e.persisted) location.reload();
    });
</script>
@endpush

@push('styles')
<style>
.conv-item {
    border-bottom: 1px solid var(--gray-100);
    transition: background 0.15s;
    display: flex; align-items: center; gap: 4px;
}
.conv-item:hover { background: var(--gray-50); }
.conv-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 10px 14px 18px;
    text-decoration: none;
    color: inherit;
    flex: 1; min-width: 0;
}
.conv-avatar {
    width: 50px; height: 50px; border-radius: 50%;
    background: var(--blue); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; font-weight: 700; flex-shrink: 0;
    overflow: hidden;
}
.conv-avatar img { width: 100%; height: 100%; object-fit: cover; }
.conv-body { flex: 1; min-width: 0; }
.conv-top {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 2px;
}
.conv-name { font-weight: 600; font-size: 0.95rem; color: var(--dark); }
.conv-time { font-size: 0.7rem; color: var(--gray-500); white-space: nowrap; }
.conv-delete-form { margin-inline-end: 8px; flex-shrink: 0; }
.conv-delete-btn {
    background: none; border: none; color: var(--gray-400);
    font-size: 0.85rem; cursor: pointer; padding: 6px; border-radius: 50%;
    transition: all 0.15s; display: flex;
}
.conv-delete-btn:hover { color: #dc3545; background: rgba(220,53,69,0.1); }
.conv-bottom {
    display: flex; justify-content: space-between; align-items: center;
    gap: 8px;
}
.conv-preview {
    font-size: 0.82rem; color: var(--gray-500);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    flex: 1;
}
</style>
@endpush
