{{-- مسكن — صفحة إعدادات النظام للمسؤول --}}
@extends('layouts.app')

@section('title', __('Settings - Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ __('System Settings') }}</h1>
</div>

<div class="maskan-card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            @method('PUT')

            <h5 class="mb-3 gold-text">{{ __('Contact Information') }}</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">{{ __('Phone Number') }}</label>
                    <input type="text" name="contact_phone" class="form-control" value="{{ $settings['contact_phone'] ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Address') }}</label>
                    <input type="text" name="contact_address" class="form-control" value="{{ $settings['contact_address'] ?? '' }}">
                </div>
            </div>

            <h5 class="mb-3 gold-text">{{ __('Terms & Conditions') }}</h5>
            <div class="mb-4">
                <textarea name="terms" class="form-control" rows="6">{{ $settings['terms'] ?? '' }}</textarea>
            </div>

            <button type="submit" class="btn btn-maskan">
                <i class="fas fa-save ms-1"></i> {{ __('Save Changes') }}
            </button>
        </form>
    </div>
</div>

<div class="maskan-card mt-4">
    <div class="card-body">
        <h5 class="mb-3 gold-text">{{ __('Manage Cities') }}</h5>
        <p class="text-muted">{{ __('Add, edit or remove cities available in the system') }}</p>
        <a href="{{ route('admin.cities') }}" class="btn btn-maskan">
            <i class="fas fa-city ms-1"></i> {{ __('Go to Cities Management') }}
        </a>
    </div>
</div>
@endsection
