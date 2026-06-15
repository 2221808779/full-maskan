{{-- مسكن — صفحة الجدول الزمني للحجوزات للمالك --}}
@extends('layouts.app')

@section('title', __('Booking Timeline - Maskan'))

@php
$today = now()->startOfDay();
@endphp

@section('content')
<div class="page-header">
    <h1>{{ __('Booking Timeline') }}</h1>
</div>

@php
$allBookings = $properties->flatMap->bookings;
$stats = [
    ['label' => __('Total Bookings'), 'value' => $allBookings->count(), 'icon' => 'fa-calendar-check', 'color' => 'blue'],
    ['label' => __('In Progress'), 'value' => $allBookings->where('status', 'in_progress')->count(), 'icon' => 'fa-play-circle', 'color' => 'green'],
    ['label' => __('Upcoming Bookings'), 'value' => $allBookings->where('status', 'confirmed')->where('start_date', '>', $today)->count(), 'icon' => 'fa-clock', 'color' => 'gold'],
    ['label' => __('Completed'),      'value' => $allBookings->whereIn('status', ['completed'])->count(), 'icon' => 'fa-check-circle', 'color' => 'dark'],
];
@endphp

<div class="kpi-grid">
    @foreach($stats as $s)
    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon {{ $s['color'] }}"><i class="fas {{ $s['icon'] }}"></i></div>
        </div>
        <div class="kpi-value">{{ $s['value'] }}</div>
        <div class="kpi-label">{{ $s['label'] }}</div>
    </div>
    @endforeach
</div>

@forelse($properties as $property)
@php $bookings = $property->bookings; @endphp
<div class="maskan-card mb-4">
    <div class="card-header-custom d-flex align-items-center justify-content-between px-4 py-3" style="border-bottom:1px solid var(--gray-100);">
        <div class="d-flex align-items-center gap-2">
            <span class="badge badge-{{ $property->status }}" style="font-size:11px;padding:2px 10px;">{{ __($property->status) }}</span>
            <span style="font-weight:700;font-size:15px;color:var(--blue);">{{ $property->title }}</span>
            <span style="font-size:12px;color:var(--gray-400);">{{ $property->location ?? '—' }}</span>
        </div>
        <span style="font-size:12px;color:var(--gray-400);">{{ $bookings->count() }} {{ __('Bookings') }}</span>
    </div>

    <div class="card-body p-0">
        @if($bookings->isEmpty())
            <div class="text-center py-4" style="color:var(--gray-400);">
                <i class="fas fa-calendar-alt fa-2x mb-2 d-block" style="color:var(--gray-200);"></i>
                {{ __('No upcoming bookings') }}
            </div>
        @else
            <div style="overflow:hidden;">
                <table class="data-table" style="margin:0;">
                    <thead>
                        <tr>
                            <th>{{ __('Tenant') }}</th>
                            <th>{{ __('Check-in') }}</th>
                            <th>{{ __('Check-out') }}</th>
                            <th>{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $booking)
                            @php
                                $bStart = \Carbon\Carbon::parse($booking->start_date);
                                $bEnd = \Carbon\Carbon::parse($booking->end_date);
                                $initials = collect(explode(' ', $booking->user->full_name ?? '?'))->map(fn($w) => mb_substr($w, 0, 1))->take(2)->implode('');
                            @endphp
                            <tr>
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <div style="width:34px;height:34px;border-radius:50%;background:var(--blue);color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0;">{{ $initials }}</div>
                                        <div>
                                            <div style="font-weight:600;color:var(--gray-800);">{{ $booking->user->full_name ?? '—' }}</div>
                                            <div style="font-size:12px;color:var(--gray-400);">{{ $bStart->translatedFormat('d F Y') }} – {{ $bEnd->translatedFormat('d F Y') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-weight:600;">{{ $bStart->translatedFormat('d F Y') }}</td>
                                <td style="font-weight:600;">{{ $bEnd->translatedFormat('d F Y') }}</td>
                                <td><span class="badge badge-{{ $booking->status }}" style="font-size:12px;padding:3px 12px;">{{ __($booking->status) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@empty
<div class="maskan-card">
    <div class="card-body text-center py-5" style="color:var(--gray-400);">
        <i class="fas fa-calendar-alt fa-3x mb-3 d-block" style="color:var(--gray-200);"></i>
        <p style="font-size:15px;">{{ __('No properties to show') }}</p>
    </div>
</div>
@endforelse
@endsection
