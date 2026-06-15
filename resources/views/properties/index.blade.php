{{-- مسكن — صفحة عرض جميع العقارات --}}
@extends('layouts.app')

@section('title', __('All Properties - Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ __('All Properties') }}</h1>
    @auth
    <a href="{{ route('properties.create') }}" class="btn btn-maskan">
        <i class="fas fa-plus-circle ms-1"></i> {{ __('Add Property') }}
    </a>
    @endauth
</div>

<!-- Search & Filter -->
<div class="table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="{{ __('Search property...') }}"
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="type" class="form-select">
                    <option value="">{{ __('All Types') }}</option>
                    <option value="resort" {{ request('type') == 'resort' ? 'selected' : '' }}>{{ __('Resort') }}</option>
                    <option value="rest_house" {{ request('type') == 'rest_house' ? 'selected' : '' }}>{{ __('Rest House') }}</option>
                    <option value="villa" {{ request('type') == 'villa' ? 'selected' : '' }}>{{ __('Villa') }}</option>
                    <option value="house" {{ request('type') == 'house' ? 'selected' : '' }}>{{ __('House') }}</option>
                    <option value="building" {{ request('type') == 'building' ? 'selected' : '' }}>{{ __('Building') }}</option>
                    <option value="apartment" {{ request('type') == 'apartment' ? 'selected' : '' }}>{{ __('Apartment') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>{{ __('available') }}</option>
                    <option value="booked" {{ request('status') == 'booked' ? 'selected' : '' }}>{{ __('booked') }}</option>
                    <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>{{ __('maintenance') }}</option>
                    <option value="unavailable" {{ request('status') == 'unavailable' ? 'selected' : '' }}>{{ __('unavailable') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-maskan w-100">
                    <i class="fas fa-search ms-1"></i> {{ __('Search') }}
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Properties Table -->
<div class="table-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Image') }}</th>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Price per Night') }}</th>
                    <th>{{ __('Bedrooms') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($properties as $property)
                <tr>
                    <td>{{ $property->id }}</td>
                    <td>
                        @if($property->images && isset($property->images[0]))
                            <img src="{{ asset($property->images[0]->image_path) }}" alt="{{ __('Image') }}"
                                 style="width: 50px; height: 40px; object-fit: cover; border-radius: 6px;">
                        @else
                            <div style="width: 50px; height: 40px; border-radius: 6px; background: #e9ecef; display: flex; align-items: center; justify-content: center; color: #adb5bd;">
                                <i class="fas fa-image"></i>
                            </div>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('properties.show', $property) }}" class="fw-bold text-decoration-none" style="color: var(--blue-dark);">
                            {{ $property->title }}
                        </a>
                        <br><small class="text-muted">{{ $property->location ?? '—' }}</small>
                    </td>
                    <td>
                        <span class="badge bg-secondary">{{ __($property->property_type) }}</span>
                    </td>
                    <td>{{ number_format($property->price, 2) }} {{ __('LYD') }}</td>
                    <td>{{ $property->rooms_count }}</td>
                    <td>
                        <span class="badge badge-{{ $property->status }}">
                            @switch($property->status)
                                @case('available') {{ __('available') }} @break
                                @case('booked') {{ __('booked') }} @break
                                @case('maintenance') {{ __('maintenance') }} @break
                                @default {{ __('unavailable') }}
                            @endswitch
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('properties.show', $property) }}" class="action-btn" title="{{ __('View') }}">
                                <i class="fas fa-eye"></i>
                            </a>
                            @auth
                            @if(auth()->user()->user_type === 'owner' && $property->owner_id === auth()->id())
                            <a href="{{ route('properties.edit', $property) }}" class="action-btn" title="{{ __('Edit') }}">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('properties.destroy', $property) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-btn danger" title="{{ __('Delete') }}"
                                        onclick="return confirm('{{ __('Are you sure?') }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                            @endauth
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="fas fa-building fa-3x mb-3 d-block"></i>
                        {{ __('No properties to show') }}
                        @auth
                        @if(auth()->user()->user_type === 'owner')
                            <br><a href="{{ route('properties.create') }}" class="btn btn-maskan mt-3">{{ __('Add your first property') }}</a>
                        @endif
                        @endauth
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    <div class="table-pagination">
        <span class="pagination-info">{{ __('Showing pagination', ['from' => $properties->firstItem() ?? 0, 'to' => $properties->lastItem() ?? 0, 'total' => $properties->total()]) }}</span>
        <div class="pagination-btns">
            {{ $properties->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
