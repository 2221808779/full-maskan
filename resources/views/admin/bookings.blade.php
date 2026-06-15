{{-- مسكن — صفحة إدارة جميع الحجوزات للمسؤول --}}
@extends('layouts.app')

@section('title', __('All Bookings - Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ __('All Bookings') }}</h1>
</div>

<div class="table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>{{ __('Confirmed') }}</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-maskan w-100"><i class="fas fa-filter ms-1"></i> {{ __('Filter') }}</button>
            </div>
        </form>
    </div>
</div>

<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('Tenant') }}</th>
                <th>{{ __('Owner') }}</th>
                <th>{{ __('Property') }}</th>
                <th>{{ __('Amount') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Date') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $booking)
            <tr>
                <td>{{ $booking->id }}</td>
                <td>{{ $booking->user->full_name ?? '—' }}</td>
                <td>{{ $booking->property->owner->full_name ?? '—' }}</td>
                <td>
                    <a href="{{ route('properties.show', $booking->property) }}" class="text-decoration-none" style="color: var(--blue-dark);">
                        {{ $booking->property->title ?? '—' }}
                    </a>
                </td>
                <td class="fw-bold" style="color: var(--gold);">{{ number_format($booking->total_price, 2) }} {{ __('LYD') }}</td>
                <td><span class="badge badge-{{ $booking->status }}">{{ __($booking->status) }}</span></td>
                <td>{{ $booking->created_at->format('Y-m-d') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-muted py-5">
                    <i class="fas fa-calendar-times fa-3x mb-3 d-block"></i>
                    {{ __('No bookings to show') }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="table-pagination">
        <span class="pagination-info">{{ __('Showing pagination', ['from' => $bookings->firstItem() ?? 0, 'to' => $bookings->lastItem() ?? 0, 'total' => $bookings->total()]) }}</span>
        <div class="pagination-btns">{{ $bookings->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
@endsection
