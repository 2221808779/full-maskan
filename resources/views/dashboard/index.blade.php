{{-- مسكن — صفحة لوحة التحكم الرئيسية --}}
@extends('layouts.app')

@section('title', __('Dashboard - Maskan'))

@section('breadcrumb')
    <span class="current">{{ __('Dashboard') }}</span>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ __('Dashboard') }}</h1>
        <p class="page-subtitle">{{ __('Welcome message', ['name' => auth()->user()->full_name]) }}</p>
    </div>
    @if(auth()->user()->user_type === 'owner')
    <a href="{{ route('properties.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> {{ __('Add Property') }}
    </a>
    @endif
</div>

@if(in_array(auth()->user()->user_type, ['admin', 'owner']))
<!-- Charts Row -->
<div class="grid-2-col" style="margin-bottom: 24px;">
    <div class="chart-card">
        <div class="chart-header">
            <span class="chart-title"><i class="fas fa-chart-bar gold-text ms-1"></i> {{ __('Monthly Bookings') }}</span>
        </div>
        <canvas id="bookingsChart" height="200"></canvas>
    </div>

    @if(auth()->user()->user_type === 'admin')
    <div class="chart-card">
        <div class="chart-header">
            <span class="chart-title"><i class="fas fa-chart-pie gold-text ms-1"></i> {{ __('Users by Role') }}</span>
        </div>
        <canvas id="usersPieChart" height="200"></canvas>
    </div>
    @else
    <div class="chart-card">
        <div class="chart-header">
            <span class="chart-title"><i class="fas fa-chart-line gold-text ms-1"></i> {{ __('Monthly Revenue') }}</span>
        </div>
        <canvas id="revenueChart" height="200"></canvas>
    </div>
    @endif
</div>

@endif

<!-- KPI Cards -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon blue"><i class="fas fa-building"></i></div>
            @isset($deltas['properties'])
            <span class="kpi-delta {{ $deltas['properties']['dir'] }}">
                <i class="fas fa-arrow-{{ $deltas['properties']['dir'] }}"></i> {{ $deltas['properties']['pct'] }}%
            </span>
            @endisset
        </div>
        <div class="kpi-value">{{ $stats['properties_count'] ?? 0 }}</div>
        <div class="kpi-label">{{ __('Total Properties') }}</div>
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

    @unless(auth()->user()->user_type === 'admin')
    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon dark"><i class="fas fa-money-bill-wave"></i></div>
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
    @endunless

    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon green"><i class="fas fa-tools"></i></div>
        </div>
        <div class="kpi-value">{{ $stats['maintenance_count'] ?? 0 }}</div>
        <div class="kpi-label">{{ __('Maintenance Requests') }}</div>
        @if(($stats['maintenance_pending'] ?? 0) > 0)
        <div class="kpi-sub" style="color: var(--gold);">
            {{ $stats['maintenance_pending'] }} {{ __('pending') }}
        </div>
        @endif
    </div>
</div>

@if(in_array(auth()->user()->user_type, ['admin', 'owner']))
<!-- Property Status Breakdown -->
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
        <div style="width: 6px; height: 40px; border-radius: 4px; background: var(--gold); flex-shrink: 0;">
    </div>
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

@if(auth()->user()->user_type === 'admin' && $pendingProperties->isNotEmpty())
<div class="maskan-card" style="margin-bottom: 24px;">
    <div class="table-toolbar">
        <span class="table-title"><i class="fas fa-clock gold-text ms-1"></i> {{ __('Pending Properties') }}</span>
        <a href="{{ route('admin.properties') }}" class="btn btn-sm btn-outline">{{ __('View All') }}</a>
    </div>
    <div style="padding: 24px 28px;">
        @foreach($pendingProperties as $prop)
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--gray-100);">
            <div>
                <div style="font-weight: 600; color: var(--gray-800);">{{ $prop->title }}</div>
                <div style="font-size: 13px; color: var(--gray-400);">{{ $prop->owner->full_name ?? '—' }}</div>
            </div>
            <a href="{{ route('admin.properties') }}?status=pending" class="btn btn-sm btn-primary">{{ __('Review') }}</a>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- Recent Bookings -->
<div class="table-card" style="margin-bottom: 24px;">
    <div class="table-toolbar">
        <span class="table-title"><i class="fas fa-clock gold-text ms-1"></i> {{ __('Recently Booked') }}</span>
        <a href="{{ route('bookings.index') }}" class="btn btn-sm btn-outline">{{ __('View All') }}</a>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('Tenant') }}</th>
                <th>{{ __('Property') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentBookings ?? [] as $booking)
            <tr>
                <td>{{ $booking->id }}</td>
                <td>{{ $booking->user->full_name ?? '—' }}</td>
                <td>{{ $booking->property->title ?? '—' }}</td>
                <td><span class="badge badge-{{ $booking->status }}">{{ __($booking->status) }}</span></td>
                <td>{{ number_format($booking->total_price, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center text-muted py-4">{{ __('No bookings yet') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endif

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
    const success = getStyle('--success') || '#1A8F4C';
    const danger = getStyle('--danger') || '#C0392B';
    const gray400 = getStyle('--gray-400') || '#989EA7';
    const textColor = isDark ? '#e5e7eb' : '#2C3138';
    const gridColor = isDark ? '#2A2E36' : '#EDEEF0';

    Chart.defaults.font.family = "'Cairo', sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = gray400;

    @if(in_array(auth()->user()->user_type, ['admin', 'owner']))
    // Bookings Bar Chart
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

    @if(auth()->user()->user_type === 'admin')
    // Users by Role Pie Chart
    var roleLabels = {!! json_encode(array_map(function($r) { return __($r); }, array_keys($usersByRole ?? ['admin' => 0]))) !!};
    new Chart(document.getElementById('usersPieChart'), {
        type: 'doughnut',
        data: {
            labels: roleLabels,
            datasets: [{
                data: {!! json_encode(array_values($usersByRole ?? [0])) !!},
                backgroundColor: [gold, success, blue, danger],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            cutout: '75%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 16, usePointStyle: true }
                }
            }
        }
    });
    @else
    // Revenue Line Chart
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
    @endif

    @endif
});
</script>
@endpush
