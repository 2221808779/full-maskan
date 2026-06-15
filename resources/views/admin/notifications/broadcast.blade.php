{{-- مسكن — صفحة إرسال إشعار جماعي لجميع المستخدمين --}}
@extends('layouts.app')

@section('title', __('Broadcast Notification - Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ __('Send Broadcast') }}</h1>
</div>

<div class="maskan-card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.notifications.broadcast.send') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">{{ __('Notification Title') }}</label>
                <input type="text" name="title" class="form-control" required maxlength="255">
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('Notification Body') }}</label>
                <textarea name="content" class="form-control" rows="5" required></textarea>
            </div>

            <button type="submit" class="btn btn-maskan">
                <i class="fas fa-paper-plane ms-1"></i> {{ __('Send to All Users') }}
            </button>
        </form>
    </div>
</div>
@endsection