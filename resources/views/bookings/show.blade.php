{{-- مسكن — صفحة عرض تفاصيل الحجز --}}
@extends('layouts.app')

@section('title', __('Booking Details - Maskan'))

@php $ar = app()->getLocale() === 'ar'; @endphp

@section('content')
<div class="page-header">
    <h1>{{ __('Booking') }} {{ $booking->id }}</h1>
    <a href="{{ route('bookings.index') }}" class="btn btn-outline-gold">
        <i class="fas fa-arrow-right ms-1"></i> {{ __('Back') }}
    </a>
</div>

{{-- Status Badge --}}
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 p-2 rounded-3"
     style="background:var(--white); border:1px solid var(--gray-100); box-shadow:var(--shadow-sm);">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <span class="badge badge-{{ $booking->status }}" style="font-size:0.9rem; padding:4px 14px;">
            {{ __($booking->status) }}
        </span>
        <span style="font-size:13px; color:var(--gray-600);">
            <i class="fas fa-user ms-1"></i> {{ $booking->user->full_name ?? '—' }}
        </span>
        <span style="font-size:13px; color:var(--gray-600);">
            <i class="fas fa-calendar ms-1"></i>
            {{ \Carbon\Carbon::parse($booking->start_date)->translatedFormat('d F') }} — {{ \Carbon\Carbon::parse($booking->end_date)->translatedFormat('d F Y') }}
        </span>
        <span style="font-size:13px; font-weight:700; color:var(--gold);">
            {{ number_format($booking->total_price, 2) }} {{ __('LYD') }}
        </span>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if($booking->status === 'pending' && auth()->user()->user_type === 'owner')
            <a href="{{ route('bookings.confirm', $booking) }}" class="btn btn-maskan btn-sm"
               onclick="return confirm('{{ __('Confirm Booking') }}')">
                <i class="fas fa-check ms-1"></i> {{ __('Confirm') }}
            </a>
        @endif
        @if($booking->status === 'confirmed' && auth()->user()->user_type === 'owner')
            <a href="{{ route('bookings.checkin', $booking) }}" class="btn btn-maskan btn-sm"
               onclick="return confirm('{{ __('Confirm check-in') }}')">
                <i class="fas fa-door-open ms-1"></i> {{ __('Confirm Check-in') }}
            </a>
        @endif
        @if($booking->status === 'in_progress' && auth()->user()->user_type === 'owner')
            <a href="{{ route('bookings.complete', $booking) }}" class="btn btn-maskan btn-sm"
               onclick="return confirm('{{ __('Complete Booking') }}')">
                <i class="fas fa-check-double ms-1"></i> {{ __('Complete') }}
            </a>
        @endif
        @if($booking->status === 'confirmed' && auth()->id() === $booking->user_id)
            @php $payment = \App\Models\Payment::where('booking_id', $booking->id)->latest()->first(); @endphp
            @if(!$payment || $payment->status !== 'completed')
                <a href="{{ route('plutu.pay', $booking) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-credit-card ms-1"></i> {{ __('Pay Now') }}
                </a>
            @else
                <span class="badge badge-completed" style="font-size:0.85rem; padding:6px 14px;">
                    <i class="fas fa-check-circle ms-1"></i> {{ __('Paid') }}
                </span>
            @endif
        @endif
        @if(in_array($booking->status, ['pending', 'confirmed', 'in_progress']))
            <form action="{{ route('bookings.cancel', $booking) }}" method="POST" class="d-inline"
                  onsubmit="var r=prompt('{{ __('Cancellation reason') }}:'); if(r===null)return false; var i=document.createElement('input'); i.type='hidden'; i.name='reason'; i.value=r; this.appendChild(i); return confirm('{{ __('Cancel Booking') }}')">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-times ms-1"></i> {{ __('Cancel') }}
                </button>
            </form>
        @endif
    </div>
</div>

{{-- Main Card --}}
<div class="maskan-card">
    <div class="card-body">
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:8px 24px;">
            <div>
                <small class="text-muted">{{ __('Property') }}</small>
                <div style="font-weight:600; color:var(--blue-dark); font-size:14px;">
                    <a href="{{ route('properties.show', $booking->property) }}" class="text-decoration-none" style="color:var(--blue-dark);">
                        {{ $booking->property->title ?? '—' }}
                    </a>
                </div>
            </div>
            <div>
                <small class="text-muted">{{ __('Type') }}</small>
                <div style="font-weight:600; font-size:14px;">{{ __($booking->property->property_type) }}</div>
            </div>
            <div>
                <small class="text-muted">{{ __('Location') }}</small>
                <div style="font-weight:600; font-size:14px;">{{ $booking->property->location ?? '—' }}</div>
            </div>
            <div>
                <small class="text-muted">{{ __('Owner') }}</small>
                <div style="font-weight:600; font-size:14px;">{{ $booking->property->owner->full_name ?? '—' }}</div>
            </div>
            <div>
                <small class="text-muted">{{ __('Check-in') }}</small>
                <div style="font-weight:600; font-size:14px;">{{ \Carbon\Carbon::parse($booking->start_date)->translatedFormat('d F Y') }}</div>
            </div>
            <div>
                <small class="text-muted">{{ __('Check-out') }}</small>
                <div style="font-weight:600; font-size:14px;">{{ \Carbon\Carbon::parse($booking->end_date)->translatedFormat('d F Y') }}</div>
            </div>
            <div>
                <small class="text-muted">{{ __('Nights') }}</small>
                <div style="font-weight:600; font-size:14px;">{{ \Carbon\Carbon::parse($booking->start_date)->diffInDays(\Carbon\Carbon::parse($booking->end_date)) }}</div>
            </div>
            <div>
                <small class="text-muted">{{ __('Price per night') }}</small>
                <div style="font-weight:600; font-size:14px;">{{ number_format($booking->property->price, 2) }} {{ __('LYD') }}</div>
            </div>
            <div>
                <small class="text-muted">{{ __('Total Price') }}</small>
                <div style="font-weight:700; color:var(--gold); font-size:15px;">{{ number_format($booking->total_price, 2) }} {{ __('LYD') }}</div>
            </div>
        </div>

        @if($booking->payment)
        <hr style="margin:16px 0; border-style:dashed;">
        <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:8px;">
            <div>
                <small class="text-muted">{{ __('Payment') }}</small>
                <div style="font-weight:600; font-size:14px;">{{ number_format($booking->payment->amount, 2) }} {{ __('LYD') }}</div>
            </div>
            <div>
                <small class="text-muted">{{ __('Method') }}</small>
                <div style="font-weight:600; font-size:14px;">{{ __($booking->payment->payment_type) }}</div>
            </div>
            <div>
                <small class="text-muted">{{ __('Payment Status') }}</small>
                <div style="font-weight:600; font-size:14px;"><span class="badge badge-{{ $booking->payment->status }}">{{ __($booking->payment->status) }}</span></div>
            </div>
        </div>
        @endif

        @if($booking->cancellation_reason)
        <hr style="margin:16px 0; border-style:dashed;">
        <div>
            <small class="text-muted d-block mb-1">{{ __('Cancellation reason') }}</small>
            <p style="margin:0; background:var(--gray-50); padding:8px 12px; border-radius:8px; font-size:13px; color:var(--danger);">
                {{ $booking->cancellation_reason }}
            </p>
        </div>
        @endif

        @if($booking->notes)
        <hr style="margin:16px 0; border-style:dashed;">
        <div>
            <small class="text-muted d-block mb-1">{{ __('Notes') }}</small>
            <p style="margin:0; background:var(--gray-50); padding:8px 12px; border-radius:8px; font-size:13px;">
                {{ $booking->notes }}
            </p>
        </div>
        @endif

    </div>
</div>

@if($review)
<div class="maskan-card mt-3">
    <div class="card-body">
        <h5 class="mb-2" style="color:var(--gold); font-size:15px;">
            {{ __('Tenant Rating') }}
        </h5>
        <div class="d-flex align-items-center gap-2 mb-1">
            <div style="font-size:1.2rem;">
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= $review->stars)
                        <i class="fas fa-star" style="color:var(--gold);"></i>
                    @else
                        <i class="far fa-star" style="color:var(--gray-400);"></i>
                    @endif
                @endfor
            </div>
            <span style="font-weight:700; font-size:1.1rem; color:var(--gold);">{{ $review->stars }}/5</span>
        </div>
        @if($review->comment)
            <p style="background:var(--gray-50); padding:10px 14px; border-radius:10px; margin:0; font-size:13px; color:var(--gray-700);">
                {{ $review->comment }}
            </p>
        @endif
    </div>
</div>
@endif
@endsection
