{{-- مسكن — صفحة إدارة عقارات المالك --}}
@extends('layouts.app')

@section('title', __('My Properties - Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ __('My Properties') }}</h1>
    <a href="{{ route('properties.create') }}" class="btn btn-maskan">
        <i class="fas fa-plus-circle ms-1"></i> {{ __('Add Property') }}
    </a>
</div>

<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('Title') }}</th>
                <th>{{ __('Location') }}</th>
                <th>{{ __('Price') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Bookings') }}</th>
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
                <td>{{ $property->location ?? '—' }}</td>
                <td class="fw-bold" style="color: var(--gold);">{{ number_format($property->price, 2) }} {{ __('LYD') }}</td>
                <td><span class="badge badge-{{ $property->status }}">{{ __($property->status) }}</span></td>
                <td>{{ $property->bookings_count }}</td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="{{ route('properties.show', $property) }}" class="action-btn" title="{{ __('View') }}">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('properties.edit', $property) }}" class="action-btn" title="{{ __('Edit') }}">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="{{ route('owner.properties.availability', $property) }}" class="action-btn" title="{{ __('Calendar') }}">
                            <i class="fas fa-calendar-alt"></i>
                        </a>
                        @if($property->status === 'unavailable')
                            <form action="{{ route('owner.properties.toggle-status', $property) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('{{ __('Reactivate this property and show it in search results?') }}')">
                                @csrf
                                <button type="submit" class="action-btn success" title="{{ __('Reactivate') }}">
                                    <i class="fas fa-play"></i>
                                </button>
                            </form>
                        @else
                            <form action="{{ route('owner.properties.toggle-status', $property) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('{{ __('Deactivate this property? It will be hidden from search results.') }}')">
                                @csrf
                                <button type="submit" class="action-btn danger" title="{{ __('Deactivate') }}">
                                    <i class="fas fa-pause"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-muted py-5">
                    <i class="fas fa-building fa-3x mb-3 d-block"></i>
                    {{ __('No properties to show') }}
                    <br><a href="{{ route('properties.create') }}" class="btn btn-maskan mt-3">{{ __('Add your first property') }}</a>
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
