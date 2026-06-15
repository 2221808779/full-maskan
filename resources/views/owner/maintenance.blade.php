{{-- مسكن — صفحة عرض طلبات الصيانة للمالك --}}
@extends('layouts.app')

@section('title', __('Maintenance Requests - Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ __('Maintenance Requests') }}</h1>
</div>

@if(isset($upcomingPredictions) && $upcomingPredictions->isNotEmpty())
<div class="table-card" style="margin-top:24px;">
    <div class="table-toolbar">
        <span class="table-title"><i class="fas fa-robot gold-text ms-1"></i> {{ __('Predictive Maintenance') }}</span>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>{{ __('Property') }}</th>
                <th>{{ __('Predicted Category') }}</th>
                <th>{{ __('Predicted Date') }}</th>
                <th>{{ __('Days Left') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($upcomingPredictions as $pred)
            <tr>
                <td>
                    <a href="{{ route('properties.show', $pred->property_id) }}" style="color:var(--blue);text-decoration:none;font-weight:600;">
                        {{ $pred->property->title ?? '—' }}
                    </a>
                </td>
                <td><span class="badge" style="background:rgba(212,175,55,0.12);color:#b8960f;">{{ __($pred->predicted_category) }}</span></td>
                <td>{{ $pred->predicted_date->translatedFormat('d F Y') }}</td>
                <td>
                    @php $days = now()->startOfDay()->diffInDays($pred->predicted_date, false); @endphp
                    @if($days <= 0)
                        <span class="badge badge-completed">{{ __('Overdue') }}</span>
                    @elseif($days <= 7)
                        <span class="badge" style="background:rgba(231,76,60,0.12);color:#e74c3c;">{{ $days }} {{ __('Days') }}</span>
                    @else
                        <span>{{ $days }} {{ __('Days') }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

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
                <th>{{ __('Tenant') }}</th>
                <th>{{ __('Category') }}</th>
                <th>{{ __('Priority') }}</th>
                <th>{{ __('Technician') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @php
            $catLabels = ['electricity' => __('electricity'), 'plumbing' => __('plumbing'), 'ac' => __('ac'), 'general' => __('general'), 'other' => __('other')];
            $priLabels = ['low' => __('low'), 'medium' => __('medium'), 'high' => __('high'), 'urgent' => __('urgent')];
            $priColors = ['low' => 'secondary', 'medium' => 'info', 'high' => 'warning', 'urgent' => 'danger'];
            @endphp
            @forelse($requests as $req)
            <tr>
                <td>{{ $req->id }}</td>
                <td>
                    <a href="{{ route('owner.maintenance.show', $req) }}" class="fw-bold text-decoration-none" style="color: var(--blue-dark);">
{{ $req->problem_description }}
                    </a>
                </td>
                <td>{{ $req->property->title ?? '—' }}</td>
                <td>{{ $req->tenant->full_name ?? '—' }}</td>
                <td>
                    <span class="badge bg-secondary">{{ $catLabels[$req->ai_category ?? $req->category] ?? $req->ai_category ?? $req->category }}</span>
                    @if($req->ai_category)
                        <i class="fas fa-robot ms-3 gold-text" title="{{ __('AI categorized') }}"></i>
                    @endif
                </td>
                <td><span class="badge bg-{{ $priColors[$req->priority] ?? 'secondary' }}">{{ $priLabels[$req->priority] ?? $req->priority ?? '—' }}</span></td>
                <td>{{ $req->technician->full_name ?? '—' }}</td>
                <td><span class="badge badge-{{ $req->status }}">{{ __($req->status) }}</span></td>
                <td>
                    <a href="{{ route('owner.maintenance.show', $req) }}" class="action-btn" title="{{ __('View') }}">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center text-muted py-5">
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
