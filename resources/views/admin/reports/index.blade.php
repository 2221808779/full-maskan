{{-- مسكن — صفحة التقارير والإحصائيات للمسؤول --}}
@extends('layouts.app')

@section('title', __('Reports - Maskan'))

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
@endpush

@push('styles')
<style>
@media print {
    @page { margin: 2.5cm 2cm; }
    html, body { height: auto; overflow: visible; font-family: 'Times New Roman', 'Cairo', serif; }
    body * { visibility: hidden; }
    .print-area, .print-area * { visibility: visible; }
    .print-area { position: static; margin: 0; padding: 0; }
    .no-print { display: none !important; }
    nav, header, .navbar, .sidebar, .main-wrap > .navbar, aside { display: none !important; }
    .main-wrap { margin: 0 !important; padding: 0 !important; }

    .print-area::before {
        content: "{{ __('Reports & Statistics') }}" " - Maskan";
        display: block;
        font-size: 18pt;
        font-weight: 700;
        text-align: center;
        margin-bottom: 4px;
        color: #000;
    }
    .print-area::after {
        content: "{{ __('Generated on') }}: " attr(data-date);
        display: block;
        font-size: 10pt;
        text-align: center;
        color: #555;
        margin-bottom: 30px;
        border-bottom: 2px solid #000;
        padding-bottom: 12px;
    }

    .kpi-grid { display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 30px; justify-content: center; }
    .kpi-card {
        flex: 0 0 auto; width: 180px; padding: 15px; text-align: center;
        border: 1px solid #ccc; break-inside: avoid; page-break-inside: avoid;
        background: #fff !important; box-shadow: none;
    }
    .kpi-icon { display: none; }
    .kpi-value { font-size: 22pt; font-weight: 700; color: #000; margin: 5px 0; }
    .kpi-label { font-size: 10pt; color: #333; text-transform: uppercase; letter-spacing: 0.5px; }
    .kpi-top { display: block; }
    .kpi-delta { display: none; }
    .kpi-sub { font-size: 9pt; color: #666; }

    div[style*="grid-template-columns"] { display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 30px; }
    div[style*="grid-template-columns"] > .maskan-card { flex: 1 1 280px; border: 1px solid #ccc; padding: 15px; background: #fff; }
    .table-toolbar { border-bottom: 1px solid #000; margin-bottom: 12px; padding-bottom: 6px; }
    .table-title { font-size: 12pt; font-weight: 700; color: #000; }
    .table-toolbar a, .table-toolbar .btn { display: none; }
    div[style*="padding: 20px 24px"] { padding: 10px 0 !important; }
    div[style*="height: 8px"] { display: none; }
    div[style*="gap: 12px"] span:last-child { font-weight: 700; color: #000; }
    div[style*="margin-bottom: 16px"] { margin-bottom: 8px; }
    span[style*="color: var(--gray-600)"] { color: #333 !important; font-size: 10pt; }

    .table-card { border: 1px solid #ccc; margin-top: 20px; }
    .data-table { width: 100%; border-collapse: collapse; font-size: 10pt; }
    .data-table th {
        background: #000 !important; color: #fff !important; padding: 8px 10px;
        text-align: start; font-weight: 700; -webkit-print-color-adjust: exact; print-color-adjust: exact;
    }
    .data-table td { padding: 6px 10px; border-bottom: 1px solid #ddd; color: #000; }
    .data-table tr:nth-child(even) td { background: #f9f9f9; }
    .badge { background: transparent !important; color: #000 !important; padding: 0; font-weight: 600; }
    div[style*="background: var(--blue)"] { background: #000 !important; }
}
</style>
@endpush

@section('content')
<div class="page-header no-print">
    <h1>{{ __('Reports & Statistics') }}</h1>
</div>
<div class="print-area" data-date="{{ now()->format('Y-m-d') }}">

<div class="kpi-grid" style="margin-bottom: 28px;">
    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon blue"><i class="fas fa-users"></i></div>
        </div>
        <div class="kpi-value" style="color: var(--blue);">{{ $totalUsers }}</div>
        <div class="kpi-label">{{ __('Total Users') }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon gold"><i class="fas fa-building"></i></div>
        </div>
        <div class="kpi-value" style="color: var(--gold);">{{ $totalProperties }}</div>
        <div class="kpi-label">{{ __('Total Properties') }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon green"><i class="fas fa-calendar-check"></i></div>
        </div>
        <div class="kpi-value" style="color: #28a745;">{{ $totalBookings }}</div>
        <div class="kpi-label">{{ __('Total Bookings') }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon dark"><i class="fas fa-tools"></i></div>
        </div>
        <div class="kpi-value" style="color: var(--gray-600);">{{ $totalMaintenance }}</div>
        <div class="kpi-label">{{ __('Maintenance') }}</div>
    </div>
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
            <div class="kpi-icon green"><i class="fas fa-hourglass-half"></i></div>
        </div>
        <div class="kpi-value" style="color: #28a745;">{{ $pendingBookings }}</div>
        <div class="kpi-label">{{ __('Pending Bookings') }}</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 24px; margin-bottom: 28px;">
    <!-- Users by Role -->
    <div class="maskan-card" style="margin-bottom: 0;">
        <div class="table-toolbar" style="border-bottom-color: var(--blue-soft);">
            <span class="table-title"><i class="fas fa-users gold-text ms-1"></i> {{ __('Users by Role') }}</span>
        </div>
        <div style="padding: 28px 32px;">
            @foreach(['admin', 'owner', 'tenant', 'technician'] as $role)
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <span style="color: var(--gray-600); font-size: 14px;">{{ __($role) }}</span>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 120px; height: 8px; background: var(--gray-100); border-radius: 6px; overflow: hidden;">
                        @php $max = $usersByRole->max() ?: 1; @endphp
                        <div style="width: {{ ($usersByRole[$role] ?? 0) / $max * 100 }}%; height: 100%; background: var(--blue); border-radius: 6px; transition: width 0.3s;"></div>
                    </div>
                    <span style="font-weight: 700; color: var(--gray-800); min-width: 24px; text-align: center;">{{ $usersByRole[$role] ?? 0 }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Properties by Status -->
    <div class="maskan-card" style="margin-bottom: 0;">
        <div class="table-toolbar" style="border-bottom-color: var(--blue-soft);">
            <span class="table-title"><i class="fas fa-building gold-text ms-1"></i> {{ __('Properties by Status') }}</span>
        </div>
        <div style="padding: 28px 32px;">
            @php $pColors = ['available' => '#28a745', 'booked' => 'var(--blue)', 'maintenance' => 'var(--gold)', 'pending' => '#dc3545', 'unavailable' => '#dc3545'] @endphp
            @foreach($propertiesByStatus as $key => $count)
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <span style="color: var(--gray-600); font-size: 14px;">{{ __($key) }}</span>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 120px; height: 8px; background: var(--gray-100); border-radius: 6px; overflow: hidden;">
                        @php $max = $propertiesByStatus->max() ?: 1; $color = $pColors[$key] ?? 'var(--gray-400)'; @endphp
                        <div style="width: {{ $count / $max * 100 }}%; height: 100%; background: {{ $color }}; border-radius: 6px; transition: width 0.3s;"></div>
                    </div>
                    <span style="font-weight: 700; color: var(--gray-800); min-width: 24px; text-align: center;">{{ $count }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Bookings by Status -->
    <div class="maskan-card" style="margin-bottom: 0;">
        <div class="table-toolbar" style="border-bottom-color: var(--blue-soft);">
            <span class="table-title"><i class="fas fa-calendar gold-text ms-1"></i> {{ __('Bookings by Status') }}</span>
        </div>
        <div style="padding: 28px 32px;">
            @php $bColors = ['pending' => '#ffc107', 'confirmed' => '#28a745', 'completed' => 'var(--blue)', 'cancelled' => '#dc3545'] @endphp
            @foreach($bookingsByStatus as $key => $count)
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <span style="color: var(--gray-600); font-size: 14px;">{{ __($key) }}</span>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 120px; height: 8px; background: var(--gray-100); border-radius: 6px; overflow: hidden;">
                        @php $max = $bookingsByStatus->max() ?: 1; $color = $bColors[$key] ?? 'var(--gray-400)'; @endphp
                        <div style="width: {{ $count / $max * 100 }}%; height: 100%; background: {{ $color }}; border-radius: 6px; transition: width 0.3s;"></div>
                    </div>
                    <span style="font-weight: 700; color: var(--gray-800); min-width: 24px; text-align: center;">{{ $count }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 28px;">
    <div class="maskan-card" style="margin-bottom: 0;">
        <div class="chart-header" style="padding: 20px 24px 0; border: none;">
            <span class="chart-title"><i class="fas fa-chart-line gold-text ms-1"></i> {{ __('Monthly Revenue') }}</span>
        </div>
        <div style="padding: 24px 28px 28px;">
            <canvas id="revenueChart" height="180"></canvas>
        </div>
    </div>
    <div class="maskan-card" style="margin-bottom: 0;">
        <div class="table-toolbar" style="border-bottom-color: var(--blue-soft);">
            <span class="table-title"><i class="fas fa-wallet gold-text ms-1"></i> {{ __('Revenue Summary') }}</span>
        </div>
        <div style="padding: 28px 32px;">
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
</div>

<!-- Recent Bookings -->
<div class="table-card">
    <div class="table-toolbar">
        <span class="table-title"><i class="fas fa-clock gold-text ms-1"></i> {{ __('Recent Bookings') }}</span>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('Tenant') }}</th>
                <th>{{ __('Property') }}</th>
                <th>{{ __('Amount') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Date') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentBookings as $booking)
            <tr>
                <td>{{ $booking->id }}</td>
                <td>{{ $booking->user->full_name ?? '—' }}</td>
                <td>{{ $booking->property->title ?? '—' }}</td>
                <td>{{ number_format($booking->total_price, 2) }}</td>
                <td><span class="badge badge-{{ $booking->status }}">{{ __($booking->status) }}</span></td>
                <td>{{ $booking->created_at->format('Y-m-d') }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted py-4">{{ __('No bookings found') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
</div> {{-- end print-area --}}
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
