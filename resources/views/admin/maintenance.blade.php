{{-- مسكن — صفحة إدارة جميع طلبات الصيانة للمسؤول --}}
@extends('layouts.app')

@section('title', __('All Maintenance - Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ __('All Maintenance') }}</h1>
</div>

<div class="table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                    <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>{{ __('Assigned') }}</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
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
                <th>{{ __('Title') }}</th>
                <th>{{ __('Property') }}</th>
                <th>{{ __('Owner') }}</th>
                <th>{{ __('Tenant') }}</th>
                <th>{{ __('Category') }}</th>
                <th>{{ __('Technician') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $req)
            <tr>
                <td>{{ $req->id }}</td>
                <td>
                    <a href="{{ route('maintenance.show', $req) }}" class="fw-bold text-decoration-none" style="color: var(--blue-dark);">
{{ $req->problem_description }}
                    </a>
                </td>
                <td>{{ $req->property->title ?? '—' }}</td>
                <td>{{ $req->property->owner->full_name ?? '—' }}</td>
                <td>{{ $req->tenant->full_name ?? '—' }}</td>
                <td>
                    <span class="badge bg-secondary">{{ __($req->ai_category ?? $req->category) }}</span>
                    @if($req->ai_accuracy)
                        <span class="badge bg-info ms-1" title="AI: {{ $req->ai_accuracy * 100 }}%">{{ number_format($req->ai_accuracy * 100, 0) }}%</span>
                    @endif
                </td>
                <td>{{ $req->technician->full_name ?? '—' }}</td>
                <td><span class="badge badge-{{ $req->status }}">{{ __($req->status) }}</span></td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center text-muted py-5">
                    <i class="fas fa-tools fa-3x mb-3 d-block"></i>
                    {{ __('No maintenance requests') }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="table-pagination">
        <span class="pagination-info">{{ __('Showing pagination', ['from' => $requests->firstItem() ?? 0, 'to' => $requests->lastItem() ?? 0, 'total' => $requests->total()]) }}</span>
        <div class="pagination-btns">{{ $requests->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
@endsection