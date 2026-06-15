{{-- مسكن — صفحة مراجعة عقار معتمد (موافقة/رفض) للمسؤول --}}
@extends('layouts.app')

@section('title', __('Review Property - Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ __('Review Property') }}</h1>
    <a href="{{ route('admin.properties.pending') }}" class="btn btn-outline-gold">
        <i class="fas fa-arrow-right ms-1"></i> {{ __('Back') }}
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="maskan-card mb-4">
            <div class="card-header"> {{ $property->title }}</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">{{ __('Type') }}</label>
                        <p class="fw-bold">{{ __($property->property_type) }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">{{ __('Price per Night') }}</label>
                        <p class="fw-bold" style="color: var(--gold);">{{ number_format($property->price, 2) }} {{ __('LYD') }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">{{ __('Location') }}</label>
                        <p>{{ $property->location ?? '—' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">{{ __('Owner') }}</label>
                        <p>{{ $property->owner->full_name ?? '—' }}</p>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="text-muted small">{{ __('Description') }}</label>
                        <p>{{ $property->description }}</p>
                    </div>
                </div>

                @if($property->images && count($property->images) > 0)
                <label class="text-muted small">{{ __('Images') }}</label>
                <div class="property-gallery mt-2">
                    @foreach($property->images as $image)
                    <img src="{{ asset($image->image_path) }}" alt="{{ __('Image') }}" style="width: 100%; height: 150px; object-fit: cover; border-radius: 8px;">
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="maskan-card mb-4">
            <div class="card-header"> {{ __('Review Actions') }}</div>
            <div class="card-body">
                <form action="{{ route('admin.properties.approve', $property) }}" method="POST" class="mb-3">
                    @csrf
                    <button type="submit" class="btn btn-maskan w-100">
                        <i class="fas fa-check ms-1"></i> {{ __('Approve') }}
                    </button>
                </form>

                <form action="{{ route('admin.properties.reject', $property) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <textarea name="reason" class="form-control" rows="3" placeholder="{{ __('Rejection reason') }}" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="fas fa-times ms-1"></i> {{ __('Reject') }}
                    </button>
                </form>
            </div>
        </div>

        @if($property->bookings->count() > 0)
        <div class="maskan-card">
            <div class="card-header"> {{ __('Previous Bookings') }}</div>
            <div class="card-body">
                @foreach($property->bookings as $booking)
                <div class="border-bottom pb-2 mb-2">
                    <small>{{ $booking->user->full_name ?? '—' }}</small>
                    <span class="badge badge-{{ $booking->status }} float-start">{{ __($booking->status) }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
