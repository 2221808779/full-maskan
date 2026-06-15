{{-- مسكن — صفحة تعيين كلمة مرور جديدة --}}
@extends('layouts.guest')

@section('title', __('Reset password — Maskan'))

@section('content')
    <form action="{{ route('password.reset.submit') }}" method="POST" style="margin:0 auto">
        @csrf
        <input type="hidden" name="phone" value="{{ $phone }}">

        @if(session('success'))
            <div class="alert alert-success py-2 small">{{ session('success') }}</div>
        @endif

        <div class="text-center mb-3">
            <div style="font-size:2.5rem; color:var(--gold); margin-bottom:0.5rem;">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h5 style="color:#fff; margin-bottom:0.25rem;">{{ __('Reset password') }}</h5>
            <p style="color:rgba(255,255,255,0.6); font-size:0.85rem;">
                {{ __('A verification code has been sent to') }} <strong style="color:var(--gold);">{{ $phone }}</strong>
            </p>
        </div>

        <div class="mb-3">
            <label class="form-label">{{ __('Verification code') }}</label>
            <input type="text" name="otp" class="form-control text-center @error('otp') is-invalid @enderror"
                   maxlength="6" inputmode="numeric" style="font-size:1.5rem; letter-spacing:8px;" required>
            @error('otp') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="row g-2">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">{{ __('New password') }}</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">{{ __('Confirm password') }}</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-maskan-primary w-100">{{ __('Confirm and reset') }}</button>
    </form>

    <div class="auth-links mt-3">
        <a href="{{ route('login') }}">{{ __('Back to login') }}</a>
    </div>
@endsection
