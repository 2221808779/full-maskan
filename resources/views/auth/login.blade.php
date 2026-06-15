{{-- مسكن — صفحة تسجيل الدخول --}}
@extends('layouts.guest')

@section('title', __('Login - Maskan'))

@section('content')
    @if(session('phone_unverified'))
    <div class="alert alert-warning text-center py-2 small" style="background:rgba(255,193,7,0.15); border-color:rgba(255,193,7,0.3); color:#ffc107;">
        <i class="fas fa-exclamation-triangle ms-1"></i>
        @lang('Phone not verified. Please verify your phone number.')
        <a href="{{ route('verification.notice') }}" class="d-block mt-1" style="color:#ffc107; text-decoration:underline;">
            @lang('Verify now')
        </a>
    </div>
    @endif

    <form action="{{ route('login') }}" method="POST" class="login-form" style="margin:0 auto">
        @csrf

        <div class="login-input-wrap">
            <input type="tel" name="phone" class="login-input @error('phone') error @enderror"
                   placeholder="{{ __('Phone Number') }}" value="{{ old('phone', session('unverified_phone', '')) }}" pattern="09[12348][0-9]{7}" maxlength="10" title="{{ __('Phone must start with 091, 092, 093, 094, or 098') }}" required>
            <i class="fas fa-phone login-input-icon"></i>
            @error('phone') <div class="form-error-msg">{{ $message }}</div> @enderror
        </div>

        <div class="login-input-wrap">
            <input type="password" name="password" class="login-input @error('password') error @enderror"
                   placeholder="{{ __('Password') }}" required>
            <i class="fas fa-lock login-input-icon"></i>
            @error('password') <div class="form-error-msg">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="login-btn">{{ __('Login') }}</button>

        <div class="text-center mt-2">
            <a href="{{ route('password.forgot') }}" style="color:var(--gold); font-size:0.85rem; text-decoration:none;">
                <i class="fas fa-key ms-1"></i> {{ __('Forgot password?') }}
            </a>
        </div>
    </form>

    <div class="login-divider">{{ __('Or') }}</div>

    <p class="login-footer-text">
        {{ __("Don't have an account?") }}
        <a href="{{ route('register') }}">{{ __('Create a new account') }}</a>
    </p>
@endsection
