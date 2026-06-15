{{-- مسكن — صفحة عرض حجوزات المستخدم --}}
@extends('layouts.app')

@section('title', __('All Bookings - Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ __('Bookings') }}</h1>
</div>

{{-- Filter --}}
<div class="maskan-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">{{ __('Status') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('pending') }}</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>{{ __('confirmed') }}</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>{{ __('in_progress') }}</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('cancelled') }}</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('completed') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-maskan btn-sm w-100">
                    <i class="fas fa-filter ms-1"></i> {{ __('Filter') }}
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Bookings List --}}
<div class="maskan-card p-0" style="overflow:hidden;">
    @forelse($bookings as $booking)
    <a href="{{ route('bookings.show', $booking) }}" class="text-decoration-none text-dark booking-row d-block">
        <div class="booking-item">
            <div class="booking-left">
                <div class="booking-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="booking-info">
                    <span class="booking-tenant">{{ $booking->user->full_name ?? '—' }}</span>
                    <span class="booking-property">{{ $booking->property->title ?? '—' }}</span>
                    <small class="booking-dates">
                        {{ \Carbon\Carbon::parse($booking->start_date)->translatedFormat('d F') }}
                        —
                        {{ \Carbon\Carbon::parse($booking->end_date)->translatedFormat('d F Y') }}
                    </small>
                </div>
            </div>
            <div class="booking-right">
                <span class="booking-price">{{ number_format($booking->total_price, 0) }} {{ __('LYD') }}</span>
                <span class="badge badge-{{ $booking->status }}">{{ __($booking->status) }}</span>
            </div>
        </div>
    </a>
    @empty
    <div class="text-center text-muted py-5">
        <i class="fas fa-calendar-times fa-3x mb-3 d-block" style="color:var(--gold);"></i>
        <p>{{ __('No bookings to show') }}</p>
    </div>
    @endforelse

    @if($bookings->hasPages())
    <div class="d-flex justify-content-center py-3 border-top">
        {{ $bookings->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.booking-row:not(:last-child) .booking-item {
    border-bottom: 1px solid var(--gray-100);
}
.booking-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    transition: background 0.15s;
    gap: 12px;
}
.booking-item:hover { background: var(--gray-50); }
.booking-left {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    min-width: 0;
}
.booking-avatar {
    width: 44px; height: 44px; border-radius: 50%;
    background: var(--blue); color: #fff;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.booking-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.booking-tenant { font-weight: 600; font-size: 0.9rem; }
.booking-property { font-size: 0.8rem; color: var(--gray-500); }
.booking-dates { font-size: 0.7rem; color: var(--gray-400); }
.booking-right {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}
.booking-price {
    font-weight: 700;
    color: var(--gold);
    font-size: 0.95rem;
    white-space: nowrap;
}
</style>
@endpush
