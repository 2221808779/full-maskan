{{-- مسكن — صفحة عرض فواتير المالك --}}
@extends('layouts.app')

@section('title', __('Invoices - Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ __('Invoices') }}</h1>
</div>

<div class="maskan-card p-0" style="overflow:hidden;">
    @forelse($bookings as $booking)
    <div class="invoice-row">
        <div class="invoice-left">
            <div class="invoice-icon">
                <i class="fas fa-file-pdf"></i>
            </div>
            <div class="invoice-info">
                <span class="invoice-title">
                    {{ __('Invoice') }} #{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}
                </span>
                <span class="invoice-meta">
                    {{ $booking->user->full_name ?? '—' }}
                    <span class="mx-1 text-muted">•</span>
                    {{ $booking->property->title ?? '—' }}
                </span>
                <small class="invoice-date">
                    {{ ($booking->completed_at ?? $booking->updated_at)->translatedFormat('d F Y') }}
                    <span class="mx-1 text-muted">•</span>
                    {{ $booking->updated_at->diffForHumans() }}
                </small>
            </div>
        </div>
        <div class="invoice-right">
            <span class="invoice-amount">{{ number_format($booking->total_price, 0) }} {{ __('LYD') }}</span>
            @if($booking->payment)
                <span class="badge badge-{{ $booking->payment->status }}" style="font-size:0.7rem;">
                    {{ __($booking->payment->status) }}
                </span>
            @endif
            <form action="{{ route('owner.invoices.create') }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                <button type="submit" class="invoice-download-btn" title="{{ __('Download PDF') }}">
                    <i class="fas fa-download"></i>
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="text-center text-muted py-5">
        <i class="fas fa-file-invoice fa-3x mb-3 d-block" style="color:var(--gold);"></i>
        <p>{{ __('No completed bookings yet') }}</p>
    </div>
    @endforelse
</div>

<div class="mt-4">
    {{ $bookings->links() }}
</div>
@endsection

@push('styles')
<style>
.invoice-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    border-bottom: 1px solid var(--gray-100);
    transition: background 0.15s;
    gap: 12px;
}
.invoice-row:hover { background: var(--gray-50); }
.invoice-row:last-child { border-bottom: none; }
.invoice-left {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    min-width: 0;
}
.invoice-icon {
    width: 44px; height: 44px; border-radius: 10px;
    background: rgba(220,53,69,0.1); color: #dc3545;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; flex-shrink: 0;
}
.invoice-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.invoice-title { font-weight: 600; font-size: 0.9rem; }
.invoice-meta { font-size: 0.8rem; color: var(--gray-500); }
.invoice-date { font-size: 0.7rem; color: var(--gray-400); }
.invoice-right {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}
.invoice-amount {
    font-weight: 700; color: var(--gold); font-size: 0.95rem;
    white-space: nowrap;
}
.invoice-download-btn {
    width: 36px; height: 36px; border-radius: 8px;
    background: var(--blue); color: #fff; border: none;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 0.85rem;
    transition: all 0.15s;
}
.invoice-download-btn:hover { background: #24507a; }
</style>
@endpush
