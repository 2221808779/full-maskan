{{-- مسكن — صفحة التحقق من رقم الهاتف عبر OTP --}}
@extends('layouts.guest')

@section('title', __('Verify phone number — Maskan'))

@section('content')
    <form id="verifyForm" style="margin:0 auto">
        @csrf
        <input type="hidden" name="phone" id="verifyPhone" value="{{ old('phone', session('unverified_phone', '')) }}">
        <div class="text-center mb-3">
            <div style="font-size:2.5rem; color:var(--gold); margin-bottom:0.5rem;">
                <i class="fas fa-mobile-alt"></i>
            </div>
            <h5 style="color:#fff; margin-bottom:0.25rem;">{{ __('Verify phone number') }}</h5>
            <p style="color:rgba(255,255,255,0.6); font-size:0.85rem;">
                {{ __('Enter the verification code sent to') }} <strong id="phoneDisplay" style="color:var(--gold);"></strong>
            </p>
        </div>
        <div class="mb-3">
            <label class="form-label">{{ __('Verification code') }}</label>
            <input type="text" name="otp" class="form-control text-center" maxlength="6" inputmode="numeric" autocomplete="one-time-code" style="font-size:1.5rem; letter-spacing:8px;" required>
        </div>
        <div id="verifyError" class="alert alert-danger d-none py-2 small"></div>
        <button type="submit" class="btn btn-maskan-primary" id="verifyBtn">{{ __('Confirm code') }}</button>
        <div class="text-center mt-2">
            <button type="button" id="resendBtn" class="btn btn-link" style="color:var(--gold); font-size:0.85rem;">{{ __('Resend code') }}</button>
            <span id="resendTimer" style="color:rgba(255,255,255,0.4); font-size:0.85rem; display:none;"></span>
        </div>
    </form>

    <div class="auth-links mt-3">
        <a href="{{ route('login') }}">{{ __('Back to login') }}</a>
    </div>

@push('scripts')
<script>
const phone = document.getElementById('verifyPhone').value;
document.getElementById('phoneDisplay').textContent = phone;

if (!phone) {
    window.location.href = '{{ route('login') }}';
}

startResendTimer();

document.getElementById('verifyForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('verifyBtn');
    const errorDiv = document.getElementById('verifyError');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __("Verifying...") }}';
    errorDiv.classList.add('d-none');

    const data = {
        phone: phone,
        otp: this.querySelector('[name="otp"]').value,
    };

    try {
        const response = await fetch('{{ url('/api/auth/verify-otp') }}', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();

        if (result.verified) {
            window.location.href = '{{ url('/dashboard') }}';
        } else {
            errorDiv.textContent = result.message || '{{ __("Invalid code") }}';
            errorDiv.classList.remove('d-none');
        }
    } catch (err) {
        errorDiv.textContent = '{{ __("An error occurred. Please try again.") }}';
        errorDiv.classList.remove('d-none');
    }

    btn.disabled = false;
    btn.innerHTML = '{{ __("Confirm code") }}';
});

document.getElementById('resendBtn').addEventListener('click', async function() {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __("Sending...") }}';

    try {
        await fetch('{{ url('/api/auth/send-otp') }}', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ phone })
        });
        startResendTimer();
    } catch (err) {}

    btn.disabled = false;
    btn.innerHTML = '{{ __("Resend code") }}';
});

function startResendTimer() {
    const btn = document.getElementById('resendBtn');
    const timer = document.getElementById('resendTimer');
    let seconds = 60;
    btn.style.display = 'none';
    timer.style.display = 'inline';

    const interval = setInterval(() => {
        seconds--;
        timer.textContent = '{{ __("Resend after") }} ' + seconds + ' {{ __("seconds") }}';
        if (seconds <= 0) {
            clearInterval(interval);
            timer.style.display = 'none';
            btn.style.display = 'inline';
        }
    }, 1000);
}
</script>
@endpush
@endsection
