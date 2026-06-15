{{-- مسكن — صفحة تقديم شكوى جديدة --}}
@extends('layouts.app')

@section('title', __('New Complaint - Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ __('New Complaint') }}</h1>
    <a href="{{ route('complaints.index') }}" class="btn btn-outline-gold">
        <i class="fas fa-arrow-right ms-1"></i> {{ __('Back') }}
    </a>
</div>

<div class="maskan-card">
    <div class="card-body">
        <form action="{{ route('complaints.store') }}" method="POST" class="form-maskan">
            @csrf

            <div class="mb-3">
                <label class="form-label">{{ __('Complaint Message') }} <span class="text-danger">*</span></label>
                <textarea name="message_text" class="form-control @error('message_text') is-invalid @enderror"
                          rows="5" placeholder="{{ __('Describe your complaint in detail') }}" required maxlength="500">{{ old('message_text') }}</textarea>
                <small class="text-muted">{{ __('Maximum 500 characters') }}</small>
                @error('message_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <hr>
            <button type="submit" class="btn btn-maskan px-4">
                <i class="fas fa-paper-plane ms-1"></i> {{ __('Submit Complaint') }}
            </button>
        </form>
    </div>
</div>
@endsection
