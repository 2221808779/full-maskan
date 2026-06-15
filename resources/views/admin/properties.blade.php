{{-- مسكن — صفحة إدارة جميع العقارات للمسؤول --}}
@extends('layouts.app')

@section('title', __('All Properties - Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ __('All Properties') }}</h1>
    <a href="{{ route('admin.properties.pending') }}" class="btn btn-outline-gold">
        <i class="fas fa-clock ms-1"></i> {{ __('Pending Approval') }}
    </a>
</div>

<div class="table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>{{ __('Available') }}</option>
                    <option value="booked" {{ request('status') == 'booked' ? 'selected' : '' }}>{{ __('Booked') }}</option>
                    <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>{{ __('Maintenance') }}</option>
                    <option value="unavailable" {{ request('status') == 'unavailable' ? 'selected' : '' }}>{{ __('Unavailable') }}</option>
                </select>
            </div>
            <div class="col-md-4">
                <select name="property_type" class="form-select">
                    <option value="">{{ __('All Types') }}</option>
                    <option value="resort" {{ request('property_type') == 'resort' ? 'selected' : '' }}>{{ __('Resort') }}</option>
                    <option value="rest_house" {{ request('property_type') == 'rest_house' ? 'selected' : '' }}>{{ __('Rest House') }}</option>
                    <option value="villa" {{ request('property_type') == 'villa' ? 'selected' : '' }}>{{ __('Villa') }}</option>
                    <option value="house" {{ request('property_type') == 'house' ? 'selected' : '' }}>{{ __('House') }}</option>
                    <option value="building" {{ request('property_type') == 'building' ? 'selected' : '' }}>{{ __('Building') }}</option>
                    <option value="apartment" {{ request('property_type') == 'apartment' ? 'selected' : '' }}>{{ __('Apartment') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-maskan w-100"><i class="fas fa-filter ms-1"></i> {{ __('Filter') }}</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.properties') }}" class="btn btn-outline-secondary w-100">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>

<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('Property') }}</th>
                <th>{{ __('Owner') }}</th>
                <th>{{ __('Location') }}</th>
                <th>{{ __('Price') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($properties as $property)
            <tr>
                <td>{{ $property->id }}</td>
                <td>
                    <a href="{{ route('properties.show', $property) }}" class="fw-bold text-decoration-none" style="color: var(--blue-dark);">
                        {{ $property->title }}
                    </a>
                </td>
                <td>{{ $property->owner->full_name ?? '—' }}</td>
                <td>{{ $property->location ?? '—' }}</td>
                <td class="fw-bold" style="color: var(--gold);">{{ number_format($property->price, 2) }} {{ __('LYD') }}</td>
                <td><span class="badge badge-{{ $property->status }}">{{ __($property->status) }}</span></td>
                <td style="white-space:nowrap;">
                    @if($property->status === 'pending')
                    <form action="{{ route('admin.properties.approve', $property) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="action-btn success" title="{{ __('Approve') }}">
                            <i class="fas fa-check"></i>
                        </button>
                    </form>
                    <form action="{{ route('admin.properties.reject', $property) }}" method="POST" style="display:inline;">
                        @csrf
                        <input type="hidden" name="reason" value="{{ __('Rejected by admin') }}">
                        <button type="submit" class="action-btn danger" title="{{ __('Reject') }}">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-muted py-5">
                    <i class="fas fa-building fa-3x mb-3 d-block"></i>
                    {{ __('No properties to show') }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="table-pagination">
        <span class="pagination-info">{{ __('Showing pagination', ['from' => $properties->firstItem() ?? 0, 'to' => $properties->lastItem() ?? 0, 'total' => $properties->total()]) }}</span>
        <div class="pagination-btns">{{ $properties->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
@endsection
