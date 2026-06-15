{{-- مسكن — صفحة عرض العقارات المفضلة --}}
@extends('layouts.app')

@section('title', __('Favorites - Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ __('Favorite Properties') }}</h1>
</div>

@if($favorites->count() > 0)
<div class="row g-4">
    @foreach($favorites as $fav)
    <div class="col-md-6 col-lg-4">
        <div class="maskan-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="card-title mb-0">
                        <a href="{{ route('properties.show', $fav->property) }}" class="text-decoration-none" style="color: var(--blue-dark);">
                            {{ $fav->property->title }}
                        </a>
                    </h5>
                    <a href="{{ route('favorites.destroy', $fav) }}" class="text-danger"
                       onclick="return confirm('{{ __('Remove from favorites?') }}')">
                        <i class="fas fa-trash"></i>
                    </a>
                </div>
                <p class="text-muted mb-2">
                    
                    {{ $fav->property->location ?? '—' }}
                </p>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold" style="color: var(--gold);">
                        {{ number_format($fav->property->price, 0) }} {{ __('LYD/night') }}
                    </span>
                    <span class="badge badge-{{ $fav->property->status }}">
                        @switch($fav->property->status)
                            @case('available') {{ __('available') }} @break
                            @case('booked') {{ __('booked') }} @break
                            @default {{ $fav->property->status }}
                        @endswitch
                    </span>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
<div class="mt-4">{{ $favorites->links() }}</div>
@else
<div class="text-center text-muted py-5">
    <i class="fas fa-heart-broken fa-3x mb-3 d-block"></i>
    <p>{{ __('No favorite properties') }}</p>
    <a href="{{ route('properties.index') }}" class="btn btn-maskan">{{ __('Browse Properties') }}</a>
</div>
@endif
@endsection
