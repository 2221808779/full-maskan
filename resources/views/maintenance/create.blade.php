{{-- مسكن — صفحة تقديم طلب صيانة جديد --}}
@extends('layouts.app')

@section('title', __('Maintenance Request - Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ __('New Maintenance Request') }}</h1>
    <a href="{{ route('maintenance.index') }}" class="btn btn-outline-gold">
        <i class="fas fa-arrow-right ms-1"></i> {{ __('Back') }}
    </a>
</div>

<div class="maskan-card">
    <div class="card-body">
        <form action="{{ route('maintenance.store') }}" method="POST" class="form-maskan">
            @csrf

            <div class="mb-3">
                <label class="form-label">{{ __('Active Booking') }} <span class="text-danger">*</span></label>
                <select name="booking_id" class="form-select @error('booking_id') is-invalid @enderror" required>
                    <option value="">{{ __('Select Booking') }}</option>
                    @foreach($bookings as $booking)
                        <option value="{{ $booking->id }}" {{ old('booking_id') == $booking->id ? 'selected' : '' }}>
                            {{ $booking->property->title ?? '—' }}
                            ({{ \Carbon\Carbon::parse($booking->start_date)->translatedFormat('d F') }} — {{ \Carbon\Carbon::parse($booking->end_date)->translatedFormat('d F') }})
                        </option>
                    @endforeach
                </select>
                @error('booking_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('Request Title') }} <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                       placeholder="{{ __('e.g. AC malfunction') }}" value="{{ old('title') }}" required>
                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('Problem Description') }} <span class="text-danger">*</span></label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                          rows="4" placeholder="{{ __('Describe the problem in detail') }}" required>{{ old('description') }}</textarea>
                <small class="text-muted">{{ __('The AI will automatically classify the fault type from your description') }}</small>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label class="form-label">{{ __('Priority') }} <span class="text-danger">*</span></label>
                        <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                            <option value="">{{ __('Select Priority') }}</option>
                            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>{{ __('Low') }}</option>
                            <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>{{ __('Medium') }}</option>
                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>{{ __('High') }}</option>
                            <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>{{ __('Urgent') }}</option>
                        </select>
                        @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="p-3 rounded-3 w-100" style="background:var(--gray-50); border:1px solid var(--gray-100);">
                            <small class="text-muted d-block mb-1">
                                <i class="fas fa-robot ms-1"></i> {{ __('AI Classification') }}
                            </small>
                            <span id="aiStatus" style="font-size:13px; color:var(--gray-500);">
                                {{ __('Will classify after submission') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <hr>
            <button type="submit" class="btn btn-maskan px-4">
                <i class="fas fa-paper-plane ms-1"></i> {{ __('Send Request') }}
            </button>
        </form>
    </div>
</div>
@endsection