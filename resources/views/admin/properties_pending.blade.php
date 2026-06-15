{{-- مسكن — صفحة عقارات بانتظار الموافقة للمسؤول --}}
@extends('layouts.app')

@section('title', __('Approval Requests - Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ __('Approval Requests') }}</h1>
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
                <th>{{ __('Request Date') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($properties as $property)
            <tr>
                <td>{{ $property->id }}</td>
                <td>
                    <a href="{{ route('admin.properties.review', $property) }}" class="fw-bold text-decoration-none" style="color: var(--blue-dark);">
                        {{ $property->title }}
                    </a>
                </td>
                <td>{{ $property->owner->full_name ?? '—' }}</td>
                <td>{{ $property->location ?? '—' }}</td>
                <td class="fw-bold" style="color: var(--gold);">{{ number_format($property->price, 2) }} {{ __('LYD') }}</td>
                <td><span class="badge badge-{{ $property->status }}">{{ __($property->status) }}</span></td>
                <td>{{ $property->created_at->format('Y-m-d') }}</td>
                <td>
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
                    <a href="{{ route('admin.properties.review', $property) }}" class="action-btn" title="{{ __('Review') }}">
                        <i class="fas fa-eye"></i>
                    </a>
                    @else
                    <a href="{{ route('admin.properties.review', $property) }}" class="action-btn" title="{{ __('View') }}">
                        <i class="fas fa-eye"></i>
                    </a>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center text-muted py-5">
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
