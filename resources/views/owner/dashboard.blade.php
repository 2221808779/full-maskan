{{-- مسكن — صفحة لوحة تحكم المالك --}}
@extends('layouts.app')

@section('title', __('Dashboard - Maskan'))

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ __('Dashboard') }}</h1>
        <p class="page-subtitle">{{ __('Welcome message', ['name' => auth()->user()->full_name]) }}</p>
    </div>
</div>

<!-- Charts Row -->
<div class="grid-2-col" style="margin-bottom: 24px;">
    <div class="chart-card">
        <div class="chart-header">
            <span class="chart-title"><i class="fas fa-chart-bar gold-text ms-1"></i> {{ __('Monthly Bookings') }}</span>
        </div>
        <canvas id="bookingsChart" height="200"></canvas>
    </div>
    <div class="chart-card">
        <div class="chart-header">
            <span class="chart-title"><i class="fas fa-chart-line gold-text ms-1"></i> {{ __('Monthly Revenue') }}</span>
        </div>
        <canvas id="revenueChart" height="200"></canvas>
    </div>
</div>

<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon blue"><i class="fas fa-building"></i></div>
        </div>
        <div class="kpi-value">{{ $stats['properties_count'] ?? 0 }}</div>
        <div class="kpi-label">{{ __('My Properties') }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon gold"><i class="fas fa-calendar-check"></i></div>
            @isset($deltas['bookings'])
            <span class="kpi-delta {{ $deltas['bookings']['dir'] }}">
                <i class="fas fa-arrow-{{ $deltas['bookings']['dir'] }}"></i> {{ $deltas['bookings']['pct'] }}%
            </span>
            @endisset
        </div>
        <div class="kpi-value">{{ $stats['bookings_count'] ?? 0 }}</div>
        <div class="kpi-label">{{ __('Total Bookings') }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon dark"><i class="fas fa-clock"></i></div>
        </div>
        <div class="kpi-value">{{ $stats['bookings_pending'] ?? 0 }}</div>
        <div class="kpi-label">{{ __('Pending Bookings') }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon green"><i class="fas fa-tools"></i></div>
        </div>
        <div class="kpi-value">{{ $stats['maintenance_pending'] ?? 0 }}</div>
        <div class="kpi-label">{{ __('Pending Maintenance') }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon gold"><i class="fas fa-money-bill-wave"></i></div>
            @isset($deltas['revenue'])
            <span class="kpi-delta {{ $deltas['revenue']['dir'] }}">
                <i class="fas fa-arrow-{{ $deltas['revenue']['dir'] }}"></i> {{ $deltas['revenue']['pct'] }}%
            </span>
            @endisset
        </div>
        <div class="kpi-value">{{ number_format($stats['total_revenue'] ?? 0) }}</div>
        <div class="kpi-label">{{ __('Total Revenue') }}</div>
        <div class="kpi-sub">{{ __('LYD') }}</div>
    </div>
</div>

<!-- Property Status Mini Row -->
<div class="kpi-grid" style="margin-bottom: 24px;">
    <div class="kpi-card" style="display: flex; align-items: center; gap: 16px; padding: 18px 20px;">
        <div style="width: 6px; height: 40px; border-radius: 4px; background: var(--success); flex-shrink: 0;"></div>
        <div>
            <div style="font-size: 22px; font-weight: 800; color: var(--gray-900);">{{ $stats['available'] ?? 0 }}</div>
            <div style="font-size: 13px; color: var(--gray-400);">{{ __('Available') }}</div>
        </div>
    </div>
    <div class="kpi-card" style="display: flex; align-items: center; gap: 16px; padding: 18px 20px;">
        <div style="width: 6px; height: 40px; border-radius: 4px; background: var(--blue); flex-shrink: 0;"></div>
        <div>
            <div style="font-size: 22px; font-weight: 800; color: var(--gray-900);">{{ $stats['booked'] ?? 0 }}</div>
            <div style="font-size: 13px; color: var(--gray-400);">{{ __('Booked') }}</div>
        </div>
    </div>
    <div class="kpi-card" style="display: flex; align-items: center; gap: 16px; padding: 18px 20px;">
        <div style="width: 6px; height: 40px; border-radius: 4px; background: var(--gold); flex-shrink: 0;"></div>
        <div>
            <div style="font-size: 22px; font-weight: 800; color: var(--gray-900);">{{ $stats['maintenance'] ?? 0 }}</div>
            <div style="font-size: 13px; color: var(--gray-400);">{{ __('Maintenance') }}</div>
        </div>
    </div>
    <div class="kpi-card" style="display: flex; align-items: center; gap: 16px; padding: 18px 20px;">
        <div style="width: 6px; height: 40px; border-radius: 4px; background: var(--danger); flex-shrink: 0;"></div>
        <div>
            <div style="font-size: 22px; font-weight: 800; color: var(--gray-900);">{{ $stats['pending_count'] ?? 0 }}</div>
            <div style="font-size: 13px; color: var(--gray-400);">{{ __('Pending') }}</div>
        </div>
    </div>
</div>

<div class="table-card">
    <div class="table-toolbar">
        <span class="table-title"><i class="fas fa-clock gold-text ms-1"></i> {{ __('Recently Booked') }}</span>
        <a href="{{ route('owner.bookings') }}" class="btn btn-sm btn-outline">{{ __('View All') }}</a>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('Tenant') }}</th>
                <th>{{ __('Property') }}</th>
                <th>{{ __('Amount') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentBookings as $booking)
            <tr>
                <td>{{ $booking->id }}</td>
                <td>{{ $booking->user->full_name ?? '—' }}</td>
                <td>{{ $booking->property->title ?? '—' }}</td>
                <td class="fw-bold" style="color: var(--gold);">{{ number_format($booking->total_price, 2) }} {{ __('LYD') }}</td>
                <td><span class="badge badge-{{ $booking->status }}">{{ __($booking->status) }}</span></td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-muted py-4">{{ __('No bookings yet') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const locale = '{{ app()->getLocale() }}';

    function getStyle(prop) {
        return getComputedStyle(document.documentElement).getPropertyValue(prop).trim();
    }

    const blue = getStyle('--blue') || '#1a3a5c';
    const gold = getStyle('--gold') || '#A3700E';
    const gray400 = getStyle('--gray-400') || '#989EA7';
    const gridColor = isDark ? '#2A2E36' : '#EDEEF0';

    Chart.defaults.font.family = "'Cairo', sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = gray400;

    new Chart(document.getElementById('bookingsChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartMonths) !!},
            datasets: [{
                label: '{{ __('Bookings') }}',
                data: {!! json_encode($chartBookings) !!},
                backgroundColor: blue + '22',
                borderColor: blue,
                borderWidth: 2,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            rtl: locale === 'ar',
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: gridColor }, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });

    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($chartMonths) !!},
            datasets: [{
                label: '{{ __('Revenue') }}',
                data: {!! json_encode($chartRevenue) !!},
                borderColor: gold,
                backgroundColor: gold + '15',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: gold,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                borderWidth: 3,
            }]
        },
        options: {
            responsive: true,
            rtl: locale === 'ar',
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: gridColor } },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>
@endpush
