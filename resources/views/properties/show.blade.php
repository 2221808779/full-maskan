{{-- مسكن — صفحة عرض تفاصيل العقار --}}
@extends('layouts.app')

@section('title', $property->title . ' - ' . __('Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ $property->title }}</h1>
    @auth
    @if(auth()->user()->id === $property->owner_id || auth()->user()->user_type === 'admin')
    <div class="d-flex gap-2 flex-wrap">
        @if(auth()->user()->id === $property->owner_id)
        <a href="{{ route('owner.properties.availability', $property) }}" class="btn btn-outline-gold">
            <i class="fas fa-calendar-alt ms-1"></i> {{ __('Availability') }}
        </a>
        @endif
        <a href="{{ route('properties.edit', $property) }}" class="btn btn-outline-gold">
            <i class="fas fa-edit ms-1"></i> {{ __('Edit') }}
        </a>
        <form action="{{ route('properties.destroy', $property) }}" method="POST" class="d-inline">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger"
                    onclick="return confirm('{{ __('Are you sure?') }}')">
                <i class="fas fa-trash ms-1"></i> {{ __('Delete') }}
            </button>
        </form>
        <a href="{{ route('properties.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-right ms-1"></i> {{ __('Back') }}
        </a>
    </div>
    @endif
    @endauth
</div>

<div class="row g-4">
    <div class="col-lg-8">
        @if($property->images)
        <div class="maskan-card mb-4">
            <div class="table-toolbar" style="border-bottom-color: var(--blue-soft);">
                <span class="table-title"><i class="fas fa-images gold-text ms-1"></i> {{ __('Property Images') }}</span>
            </div>
            <div style="padding: 28px;">
                <div class="property-gallery">
                    @foreach($property->images as $img)
                    <div class="gallery-item">
                        <img src="{{ str_starts_with($img->image_path, 'http') ? $img->image_path : asset($img->image_path) }}" alt="{{ __('Property Image') }}">
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <div class="maskan-card mb-4">
            <div class="table-toolbar" style="border-bottom-color: var(--blue-soft);">
                <span class="table-title"><i class="fas fa-info-circle gold-text ms-1"></i> {{ __('Property Details') }}</span>
            </div>
            <div style="padding: 24px;">
                <p style="color: var(--gray-600); line-height: 1.7;">{{ $property->description }}</p>
                <hr>
                <div class="property-stats-grid">
                    <div>
                        <div style="font-size: 1.5rem; font-weight: 800; color: var(--blue);">{{ $property->rooms_count }}</div>
                        <small style="color: var(--gray-400);">{{ __('Bedrooms') }}</small>
                    </div>
                    <div>
                        <div style="font-size: 1.5rem; font-weight: 800; color: var(--blue);">{{ $property->bathrooms_count }}</div>
                        <small style="color: var(--gray-400);">{{ __('Bathrooms') }}</small>
                    </div>
                    <div>
                        <div style="font-size: 1.5rem; font-weight: 800; color: var(--blue);">{{ $property->area ?? '—' }}</div>
                        <small style="color: var(--gray-400);">{{ __('sqm') }}</small>
                    </div>
                    <div>
                        <div style="font-size: 1.5rem; font-weight: 800; color: var(--gold);">{{ number_format($property->price, 2) }}</div>
                        <small style="color: var(--gray-400);">{{ __('LYD/night') }}</small>
                    </div>
                    <div>
                        <div style="font-size: 1.5rem; font-weight: 800; color: var(--gold);">{{ $property->price_per_month ? number_format($property->price_per_month, 2) : '—' }}</div>
                        <small style="color: var(--gray-400);">{{ __('LYD/month') }}</small>
                    </div>
                    <div>
                        <div style="font-size: 1.5rem; font-weight: 800; color: var(--gold);">{{ $property->deposit ? number_format($property->deposit, 2) : '—' }}</div>
                        <small style="color: var(--gray-400);">{{ __('Deposit') }}</small>
                    </div>
                </div>


            </div>
        </div>


    </div>

    <div class="col-lg-4">
        <div class="maskan-card mb-4">
            <div class="table-toolbar" style="border-bottom-color: var(--blue-soft);">
                <span class="table-title"><i class="fas fa-user gold-text ms-1"></i> {{ __('Owner') }}</span>
            </div>
            <div style="padding: 28px 32px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--blue); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <strong style="color: var(--gray-800);">{{ $property->owner->full_name ?? '—' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="maskan-card mb-4">
            <div class="table-toolbar" style="border-bottom-color: var(--blue-soft);">
                <span class="table-title"><i class="fas fa-map-marker-alt gold-text ms-1"></i> {{ __('Location') }}</span>
            </div>
            <div style="padding: 28px 32px;">
                <div style="display: flex; align-items: flex-start; gap: 10px; margin-bottom: 8px;">
                    <i class="fas fa-city" style="color: var(--gold); margin-top: 3px;"></i>
                    <div>
                        <div style="font-size: 13px; color: var(--gray-400);">{{ __('City') }}</div>
                        <div style="color: var(--gray-800); font-weight: 600;">{{ $property->location ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="maskan-card mb-4">
            <div class="table-toolbar" style="border-bottom-color: var(--blue-soft);">
                <span class="table-title"><i class="fas fa-chart-bar gold-text ms-1"></i> {{ __('Information') }}</span>
            </div>
            <div style="padding: 28px 32px;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0;">
                    <span style="color: var(--gray-400); font-size: 14px;">{{ __('Type') }}</span>
                    <span style="font-weight: 600; color: var(--gray-800);">{{ __($property->property_type) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0;">
                    <span style="color: var(--gray-400); font-size: 14px;">{{ __('Status') }}</span>
                    <span class="badge badge-{{ $property->status }}">{{ __($property->status) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0;">
                    <span style="color: var(--gray-400); font-size: 14px;">{{ __('Rating') }}</span>
                    <span style="font-weight: 600; color: var(--gray-800); display: flex; align-items: center; gap: 6px;">
                        @if($property->rating > 0)
                            @php $rounded = round($property->rating * 2) / 2; $full = floor($rounded); $half = ($rounded - $full) >= 0.25; @endphp
                            @for($i = 1; $i <= $full; $i++)
                                <i class="fas fa-star" style="color: var(--gold); font-size: 14px;"></i>
                            @endfor
                            @if($half)
                                <i class="fas fa-star-half-alt" style="color: var(--gold); font-size: 14px;"></i>
                            @endif
                            @for($i = 1; $i <= 5 - $full - ($half ? 1 : 0); $i++)
                                <i class="fas fa-star" style="color: var(--gray-200); font-size: 14px;"></i>
                            @endfor
                            <span style="margin-inline-start: 4px;">{{ number_format($property->rating, 1) }}</span>
                        @else
                            —
                        @endif
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0;">
                    <span style="color: var(--gray-400); font-size: 14px;">{{ __('Reviews Count') }}</span>
                    <span style="font-weight: 600; color: var(--gray-800);">{{ $property->review_count }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0 0; border-top: 1px solid var(--gray-100); margin-top: 6px;">
                    <span style="color: var(--gray-400); font-size: 14px;">{{ __('Added') }}</span>
                    <span style="font-weight: 600; color: var(--gray-800);">{{ $property->created_at->format('Y-m-d') }}</span>
                </div>
            </div>
        </div>

        @if($property->bookings->count() > 0 && auth()->check() && (auth()->user()->id === $property->owner_id || auth()->user()->user_type === 'admin'))
        <div class="maskan-card">
            <div class="table-toolbar" style="border-bottom-color: var(--blue-soft);">
                <span class="table-title"><i class="fas fa-calendar gold-text ms-1"></i> {{ __('Bookings') }} ({{ $property->bookings->count() }})</span>
            </div>
            <div style="padding: 8px 0;">
                @foreach($property->bookings->sortByDesc('created_at')->take(5) as $booking)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 20px; border-bottom: 1px solid var(--gray-100);">
                    <div>
                        <div style="font-weight: 600; font-size: 14px; color: var(--gray-800);">{{ $booking->user->full_name ?? '—' }}</div>
                        <div style="font-size: 12px; color: var(--gray-400);">{{ $booking->start_date ? \Carbon\Carbon::parse($booking->start_date)->translatedFormat('d F') : '' }}</div>
                    </div>
                    <span class="badge badge-{{ $booking->status }}" style="font-size: 11px;">{{ __($booking->status) }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($property->activePrediction && auth()->check() && (auth()->user()->id === $property->owner_id || auth()->user()->user_type === 'admin'))
        <div class="maskan-card mt-4">
            <div class="table-toolbar" style="border-bottom-color: var(--gold-pale);">
                <span class="table-title"><i class="fas fa-robot gold-text ms-1"></i> {{ __('Predictive Maintenance') }}</span>
            </div>
            <div style="padding: 24px 28px;">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <i class="fas fa-tools fa-2x" style="color:var(--gold);"></i>
                    <div>
                        <div style="font-weight:600;font-size:14px;color:var(--gray-800);">
                            {{ __('Next maintenance predicted') }}:
                            <span class="badge" style="background:rgba(212,175,55,0.12);color:#b8960f;">
                                {{ __($property->activePrediction->predicted_category) }}
                            </span>
                        </div>
                        <div style="font-size:13px;color:var(--gray-500);margin-top:4px;">
                            <i class="fas fa-calendar-alt ms-1"></i> {{ $property->activePrediction->predicted_date->translatedFormat('d F Y') }}
                            ({{ __('in :days days', ['days' => $property->activePrediction->days_until_next]) }})
                        </div>
                    </div>
                </div>
                <div style="font-size:12px;color:var(--gray-400);">
                    {{ __('Generated') }}: {{ $property->activePrediction->generated_at?->diffForHumans() ?? '—' }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
