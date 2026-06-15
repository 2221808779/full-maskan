{{-- مسكن — صفحة إلغاء الدفع عبر Plutu --}}
@extends('layouts.app')

@section('title', __('Payment Cancelled'))

@section('content')
<div class="page-header">
    <h1>{{ __('Payment Cancelled') }}</h1>
</div>

<div class="maskan-card text-center py-5">
    <div style="font-size: 4rem; color: #dc3545; margin-bottom: 20px;">
        <i class="fas fa-times-circle"></i>
    </div>
    <h3 class="fw-bold mb-2">{{ __('Payment was not completed') }}</h3>
    <p class="text-muted mb-3">{{ __('You can try again or choose another payment method.') }}</p>
    <div class="mt-4">
        <a href="{{ route('plutu.pay', $booking) }}" class="btn btn-maskan">
            <i class="fas fa-redo ms-1"></i> {{ __('Try Again') }}
        </a>
        <a href="{{ route('bookings.show', $booking) }}" class="btn btn-outline-gold ms-2">
            <i class="fas fa-eye ms-1"></i> {{ __('View Booking') }}
        </a>
    </div>
</div>
@endsection
