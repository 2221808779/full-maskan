{{-- مسكن — صفحة عرض تفاصيل مستخدم للمسؤول --}}
@extends('layouts.app')

@section('title', __('User Details - Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ $user->full_name }}</h1>
    <a href="{{ route('admin.users') }}" class="btn btn-outline-gold">
        <i class="fas fa-arrow-right ms-1"></i> {{ __('Back') }}
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="maskan-card text-center">
            <div class="card-body">
                <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--blue); color: white; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                    <i class="fas fa-user"></i>
                </div>
                <h5>{{ $user->full_name }}</h5>
                @php $roles = ['admin' => __('Admin'), 'owner' => __('Owner'), 'tenant' => __('Tenant'), 'technician' => __('Technician')] @endphp
                <span class="badge" style="background: rgba(45,95,138,0.15); color: var(--blue);">{{ $roles[$user->user_type] ?? '—' }}</span>
                <hr>
                <p class="mb-1 text-muted small">{{ __('Phone') }}</p>
                <p dir="ltr">{{ $user->phone ?? '—' }}</p>
                <p class="mb-1 text-muted small">{{ __('Status') }}</p>
                @if($user->status === 'suspended')
                    <span class="badge bg-danger">{{ __('Suspended') }}</span>
                @elseif($user->status === 'inactive')
                    <span class="badge bg-warning">{{ __('Inactive') }}</span>
                @else
                    <span class="badge bg-success">{{ __('Active') }}</span>
                @endif
                @if($user->ban_reason)
                    <p class="mb-1 text-muted small mt-3">{{ __('Ban reason') }}</p>
                    <p>{{ $user->ban_reason }}</p>
                @endif
                @if($user->banned_at)
                    <p class="mb-1 text-muted small">{{ __('Banned at') }}</p>
                    <p>{{ $user->banned_at->format('Y-m-d H:i') }}</p>
                @endif
                @if($user->banned_until)
                    <p class="mb-1 text-muted small">{{ __('Ban expires') }}</p>
                    <p>{{ $user->banned_until->format('Y-m-d') }}</p>
                @endif
                <p class="mb-1 text-muted small mt-3">{{ __('Registration Date') }}</p>
                <p>{{ $user->created_at->format('Y-m-d') }}</p>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        @if($user->user_type === 'owner' && $user->properties->count() > 0)
        <div class="maskan-card mb-4">
            <div class="card-header"> {{ __('Properties') }} ({{ $user->properties->count() }})</div>
            <div class="card-body">
                @foreach($user->properties as $property)
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                        <a href="{{ route('properties.show', $property) }}">{{ $property->title }}</a>
                        <span class="badge badge-{{ $property->status }}">{{ __($property->status) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($user->bookings->count() > 0)
        <div class="maskan-card mb-4">
            <div class="card-header"> {{ __('Bookings') }} ({{ $user->bookings->count() }})</div>
            <div class="card-body">
                @foreach($user->bookings->take(10) as $booking)
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                    <span>{{ $booking->property->title ?? '—' }}</span>
                    <span class="badge badge-{{ $booking->status }}">{{ __($booking->status) }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif


    </div>
</div>
@endsection
