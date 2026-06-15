{{-- مسكن — صفحة عرض حجوزات عقارات المالك --}}
@extends('layouts.app')

@section('title', __('My Bookings - Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ __('My Bookings') }}</h1>
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
                <th>{{ __('Property') }}</th>
                <th>{{ __('From') }}</th>
                <th>{{ __('To') }}</th>
                <th>{{ __('Amount') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $booking)
            <tr>
                <td>{{ $booking->id }}</td>
                <td>{{ $booking->user->full_name ?? '—' }}</td>
                <td>{{ $booking->property->title ?? '—' }}</td>
                <td>{{ \Carbon\Carbon::parse($booking->start_date)->format('Y-m-d') }}</td>
                <td>{{ \Carbon\Carbon::parse($booking->end_date)->format('Y-m-d') }}</td>
                <td class="fw-bold" style="color: var(--gold);">{{ number_format($booking->total_price, 2) }} {{ __('LYD') }}</td>
                <td><span class="badge badge-{{ $booking->status }}">{{ __($booking->status) }}</span></td>
                <td>
                    <a href="{{ route('owner.bookings.show', $booking) }}" class="action-btn" title="{{ __('View') }}">
                        <i class="fas fa-eye"></i>
                    </a>
                    @if($booking->status === 'pending')
                        <a href="{{ route('bookings.confirm', $booking) }}" class="action-btn success"
                           onclick="return confirm('{{ __('Confirm Booking') }}')" title="{{ __('Confirm') }}">
                            <i class="fas fa-check"></i>
                        </a>
                        <form action="{{ route('bookings.cancel', $booking) }}" method="POST" class="d-inline"
                              onsubmit="var r=prompt('{{ __('Cancellation reason') }}:'); if(r===null)return false; var i=document.createElement('input'); i.type='hidden'; i.name='reason'; i.value=r; this.appendChild(i); return confirm('{{ __('Cancel Booking') }}')">
                            @csrf
                            <button type="submit" class="action-btn danger" title="{{ __('Cancel') }}">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    @endif
                    @if($booking->status === 'confirmed')
                        <a href="{{ route('bookings.checkin', $booking) }}" class="action-btn"
                           onclick="return confirm('{{ __('Confirm check-in') }}')" title="{{ __('Confirm Check-in') }}">
                            <i class="fas fa-door-open"></i>
                        </a>
                    @endif
                    @if($booking->status === 'in_progress')
                        <a href="{{ route('bookings.complete', $booking) }}" class="action-btn success"
                           onclick="return confirm('{{ __('Complete Booking') }}')" title="{{ __('Mark Completed') }}">
                            <i class="fas fa-check-double"></i>
                        </a>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center text-muted py-5">
                    <i class="fas fa-calendar-times fa-3x mb-3 d-block"></i>
                    {{ __('No bookings yet') }}
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
