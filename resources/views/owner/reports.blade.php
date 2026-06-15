{{-- مسكن — صفحة التقارير المالية للمالك --}}
@extends('layouts.app')

@section('title', __('Financial Reports - Maskan'))

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
@endpush

@push('styles')
<style>
@media print {
    .no-print { display: none !important; }
    .page-header { break-after: avoid; }
    .kpi-grid { break-inside: avoid; }
    .table-card { break-inside: avoid; }
    body { background: #fff; }
}
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>{{ __('Financial Reports') }}</h1>
</div>

<div class="kpi-grid" style="margin-bottom: 24px;">
    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon gold"><i class="fas fa-money-bill-wave"></i></div>
        </div>
        <div class="kpi-value" style="color: var(--gold);">{{ number_format($totalRevenue, 2) }}</div>
        <div class="kpi-label">{{ __('Total Revenue') }}</div>
        <div class="kpi-sub">{{ __('LYD') }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon blue"><i class="fas fa-calendar-check"></i></div>
        </div>
        <div class="kpi-value" style="color: var(--blue);">{{ $completedBookings }}</div>
        <div class="kpi-label">{{ __('Completed Bookings') }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon green"><i class="fas fa-building"></i></div>
        </div>
        <div class="kpi-value" style="color: var(--success);">{{ $totalProperties }}</div>
        <div class="kpi-label">{{ __('My Properties') }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon dark"><i class="fas fa-tag"></i></div>
        </div>
        <div class="kpi-value" style="color: var(--gray-600);">{{ number_format($avgPrice ?? 0, 2) }}</div>
        <div class="kpi-label">{{ __('Average Price') }}</div>
        <div class="kpi-sub">{{ __('LYD') }}</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
    <div class="kpi-card" style="padding: 0;">
        <div class="chart-header" style="padding: 20px 24px 0; border: none;">
            <span class="chart-title"><i class="fas fa-chart-line gold-text ms-1"></i> {{ __('Monthly Revenue') }}</span>
        </div>
        <div style="padding: 24px 28px 28px;">
            <canvas id="revenueChart" height="180"></canvas>
        </div>
    </div>

    <div class="kpi-card" style="padding: 24px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
            <div class="kpi-icon gold" style="width: 36px; height: 36px; font-size: 16px;"><i class="fas fa-wallet"></i></div>
            <div>
                <div style="font-weight: 700; color: var(--gray-800); font-size: 15px;">{{ __('Revenue Summary') }}</div>
                <div style="font-size: 13px; color: var(--gray-400);">{{ __('Last 6 months') }}</div>
            </div>
        </div>
        @foreach(array_reverse($monthlyRevenue) as $mr)
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--gray-100);">
            <span style="color: var(--gray-600); font-size: 14px;">{{ $mr['month'] }}</span>
            <span style="font-weight: 700; color: var(--gold); font-size: 14px;">{{ number_format($mr['amount'], 2) }} {{ __('LYD') }}</span>
        </div>
        @endforeach
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0 0; margin-top: 4px;">
            <span style="font-weight: 700; color: var(--gray-800);">{{ __('Total') }}</span>
            <span style="font-weight: 800; color: var(--gold); font-size: 16px;">{{ number_format($totalRevenue, 2) }} {{ __('LYD') }}</span>
        </div>
    </div>
</div>

<div class="table-card">
    <div class="table-toolbar">
        <span class="table-title"><i class="fas fa-money-bill-wave gold-text ms-1"></i> {{ __('Payment Log') }}</span>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('Tenant') }}</th>
                <th>{{ __('Property') }}</th>
                <th>{{ __('Amount') }}</th>
                <th>{{ __('Payment Method') }}</th>
                <th>{{ __('Date') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
            <tr>
                <td>{{ $payment->id }}</td>
                <td>{{ $payment->booking->user->full_name ?? '—' }}</td>
                <td>{{ $payment->booking->property->title ?? '—' }}</td>
                <td class="fw-bold" style="color: var(--gold);">{{ number_format($payment->amount, 2) }} {{ __('LYD') }}</td>
                <td>{{ __($payment->payment_type) }}</td>
                <td>{{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->format('Y-m-d') : $payment->created_at->format('Y-m-d') }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted py-4">{{ __('No payments recorded') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $payments->links() }}
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const months = {!! json_encode(array_column($monthlyRevenue, 'month')) !!};
    const amounts = {!! json_encode(array_column($monthlyRevenue, 'amount')) !!};
    const gold = getComputedStyle(document.documentElement).getPropertyValue('--gold').trim() || '#A3700E';
    const locale = '{{ app()->getLocale() }}';

    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: months,
            datasets: [{
                label: '{{ __('Revenue') }}',
                data: amounts,
                backgroundColor: gold + '22',
                borderColor: gold,
                borderWidth: 2,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            rtl: locale === 'ar',
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#EDEEF0' } },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>
@endpush
