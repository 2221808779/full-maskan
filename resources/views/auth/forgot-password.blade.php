{{-- مسكن — صفحة طلب إعادة تعيين كلمة المرور --}}
@extends('layouts.guest')

@section('title', __('Forgot password — Maskan'))

@section('content')
    <form action="{{ route('password.forgot.send') }}" method="POST" style="margin:0 auto">
        @csrf

        @if(session('success'))
            <div class="alert alert-success py-2 small">{{ session('success') }}</div>
        @endif

        <div class="text-center mb-3">
            <div style="font-size:2.5rem; color:var(--gold); margin-bottom:0.5rem;">
                <i class="fas fa-key"></i>
            </div>
            <h5 style="color:#fff; margin-bottom:0.25rem;">{{ __('Forgot password') }}</h5>
            <p style="color:rgba(255,255,255,0.6); font-size:0.85rem;">
                {{ __('Enter your registered phone number to send verification code') }}
            </p>
        </div>

        <div class="mb-3">
            <label class="form-label">{{ __('Phone number') }}</label>
            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                   placeholder="0912345678" value="{{ old('phone') }}" pattern="09[12348][0-9]{7}" maxlength="10" title="{{ __('Phone must start with 091, 092, 093, 094, or 098') }}" required>
            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-maskan-primary w-100">{{ __('Send verification code') }}</button>
    </form>

    <div class="auth-links mt-3">
        <a href="{{ route('login') }}">{{ __('Back to login') }}</a>
    </div>
@endsection
