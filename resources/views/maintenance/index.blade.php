{{-- مسكن — صفحة عرض طلبات الصيانة --}}
@extends('layouts.app')

@section('title', __('Maintenance Requests - Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ __('Maintenance Requests') }}</h1>
    @if(auth()->user()->user_type === 'tenant')
    <a href="{{ route('maintenance.create') }}" class="btn btn-maskan">
        <i class="fas fa-plus-circle ms-1"></i> {{ __('Maintenance Request') }}
    </a>
    @endif
</div>

<!-- Filter -->
<div class="table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('pending') }}</option>
                    <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>{{ __('assigned') }}</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>{{ __('in_progress') }}</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('completed') }}</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('cancelled') }}</option>
                </select>
            </div>
            <div class="col-md-4">
                <select name="category" class="form-select">
                    <option value="">{{ __('All Categories') }}</option>
                    <option value="electricity" {{ request('category') == 'electricity' ? 'selected' : '' }}>{{ __('electricity') }}</option>
                    <option value="plumbing" {{ request('category') == 'plumbing' ? 'selected' : '' }}>{{ __('plumbing') }}</option>
                    <option value="ac" {{ request('category') == 'ac' ? 'selected' : '' }}>{{ __('ac') }}</option>
                    <option value="general" {{ request('category') == 'general' ? 'selected' : '' }}>{{ __('general') }}</option>
                    <option value="other" {{ request('category') == 'other' ? 'selected' : '' }}>{{ __('other') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-maskan w-100">
                    <i class="fas fa-filter ms-1"></i> {{ __('Filter') }}
                </button>
            </div>
            <div class="col-md-2">
                    <a href="{{ route('maintenance.index') }}" class="btn btn-outline-secondary w-100">{{ __('Clear Filter') }}</a>
            </div>
        </form>
    </div>
</div>

<!-- Maintenance Table -->
<div class="table-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Property') }}</th>
                    <th>{{ __('Category') }}</th>
                    <th>{{ __('Priority') }}</th>
                    <th>{{ __('Technician') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @php
                $priColors = ['low' => 'secondary', 'medium' => 'info', 'high' => 'warning', 'urgent' => 'danger'];
                @endphp
                @forelse($requests as $req)
                <tr>
                    <td>{{ $req->id }}</td>
                    <td>
                        <a href="{{ route('maintenance.show', $req) }}" class="fw-bold text-decoration-none" style="color: var(--blue-dark);">
{{ $req->problem_description }}
                        </a>
                    </td>
                    <td>
                        <a href="{{ route('properties.show', $req->property) }}" class="text-decoration-none">
                            {{ $req->property->title ?? '—' }}
                        </a>
                    </td>
                    <td>
                        <span class="badge bg-secondary">{{ __($req->ai_category ?? $req->category) }}</span>
                        @if($req->ai_accuracy)
                            <span class="badge bg-info ms-1" title="AI: {{ $req->ai_accuracy * 100 }}%">{{ number_format($req->ai_accuracy * 100, 0) }}%</span>
                        @endif
                    </td>
                    <td><span class="badge bg-{{ $priColors[$req->priority] ?? 'secondary' }}">{{ __($req->priority ?? '—') }}</span></td>
                    <td>{{ $req->technician->full_name ?? '—' }}</td>
                    <td><span class="badge badge-{{ $req->status }}">{{ __($req->status) }}</span></td>
                    <td>
                        <a href="{{ route('maintenance.show', $req) }}" class="action-btn">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
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
        <div class="pagination-btns">
            {{ $requests->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
