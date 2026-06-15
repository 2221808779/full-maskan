{{-- مسكن — صفحة نجاح الدفع عبر Plutu --}}
@extends('layouts.app')

@section('title', __('Payment Successful'))

@section('content')
<div class="page-header">
    <h1>{{ __('Payment Successful') }}</h1>
</div>

<div class="maskan-card text-center py-5">
    <div style="font-size: 4rem; color: #28a745; margin-bottom: 20px;">
        <i class="fas fa-check-circle"></i>
    </div>
    <h3 class="fw-bold mb-2">{{ __('Payment completed successfully') }}</h3>
    <p class="text-muted mb-1">{{ __('Booking') }} {{ $booking->id }}</p>
    @if(isset($transactionId))
        <p class="text-muted mb-3"><small>{{ __('Transaction ID') }}: {{ $transactionId }}</small></p>
    @endif
    <div class="mt-4">
        @auth
        <a href="{{ route('bookings.show', $booking) }}" class="btn btn-maskan">
            <i class="fas fa-eye ms-1"></i> {{ __('View Booking') }}
        </a>
        <a href="{{ route('bookings.index') }}" class="btn btn-outline-gold ms-2">
            <i class="fas fa-list ms-1"></i> {{ __('My Bookings') }}
        </a>
        @else
        <p class="text-muted">{{ __('You can now close this page') }}</p>
        @endauth
    </div>
</div>
@endsection
