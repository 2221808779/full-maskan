{{-- مسكن — صفحة عرض الإشعارات --}}
@extends('layouts.app')

@section('title', __('Notifications - Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ __('Notifications') }}</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('notifications.readAll') }}" class="btn btn-outline-gold"
           onclick="return confirm('{{ __('Mark all as read?') }}')">
            <i class="fas fa-check-double ms-1"></i> {{ __('Mark all as read') }}
        </a>
    </div>
</div>

<div class="maskan-card">
    <div class="card-body p-0 notif-list">
        @forelse($notifications as $notification)
        @php $isClickable = !empty($notification->action_url); $isUnread = is_null($notification->read_at); @endphp
        <div class="notif-item d-flex align-items-start gap-3 p-3 {{ $isClickable ? 'notif-clickable' : '' }} {{ $isUnread ? 'unread' : '' }}"
             @if($isClickable) onclick="window.location='{{ $notification->action_url }}'" style="cursor:pointer;" @endif>
            <div class="notif-icon">
                <i class="fas fa-bell"></i>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between">
                    <h6 class="mb-1">{{ $notification->title }}</h6>
                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                </div>
                <p class="mb-1 text-muted small">{{ $notification->content }}</p>
                <div class="d-flex gap-2 mt-1">
                    @if($isUnread)
                    <a href="{{ route('notifications.read', $notification) }}" class="btn btn-sm btn-link p-0 text-decoration-none text-success"
                       onclick="event.stopPropagation();">
                        <i class="fas fa-check ms-1"></i> {{ __('Mark as read') }}
                    </a>
                    @endif
                    <a href="{{ route('notifications.destroy', $notification) }}" class="btn btn-sm btn-link p-0 text-decoration-none text-danger"
                       onclick="event.stopPropagation(); return confirm('{{ __('Delete notification?') }}')">
                        <i class="fas fa-trash ms-1"></i> {{ __('Delete') }}
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center text-muted py-5">
            <i class="fas fa-bell-slash fa-3x mb-3 d-block"></i>
            {{ __('No notifications') }}
        </div>
        @endforelse
    </div>
</div>

<div class="mt-4">
    {{ $notifications->links() }}
</div>
@endsection
