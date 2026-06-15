{{-- مسكن — صفحة أرشيف العقارات المحذوفة للمسؤول --}}
@extends('layouts.app')

@section('title', __('Archive - Maskan'))

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>{{ __('Archived Bookings') }}</h1>
    <form method="POST" action="{{ route('admin.archive.run') }}" class="m-0">
        @csrf
        <button type="submit" class="btn btn-maskan">
            <i class="fas fa-box-archive ms-1"></i> {{ __('Archive Old Bookings') }}
        </button>
    </form>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('info'))
<div class="alert alert-info">{{ session('info') }}</div>
@endif

<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('Tenant') }}</th>
                <th>{{ __('Property') }}</th>
                <th>{{ __('Archived At') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($archivedBookings as $booking)
            <tr>
                <td>{{ $booking->id }}</td>
                <td>{{ $booking->user->full_name ?? '—' }}</td>
                <td>{{ $booking->property->title ?? '—' }}</td>
                <td>{{ $booking->archived_at->format('Y-m-d H:i') }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.archive.restore', $booking) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="action-btn" title="{{ __('Restore') }}">
                            <i class="fas fa-undo"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center text-muted py-5">
                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                    {{ __('No archived bookings') }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="table-pagination">
        <span class="pagination-info">{{ __('Showing pagination', ['from' => $archivedBookings->firstItem() ?? 0, 'to' => $archivedBookings->lastItem() ?? 0, 'total' => $archivedBookings->total()]) }}</span>
        <div class="pagination-btns">
            {{ $archivedBookings->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection