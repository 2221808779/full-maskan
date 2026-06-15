{{-- مسكن — صفحة تسجيل مالك عقار جديد --}}
@extends('layouts.guest')
 
@section('title', __('Create new account — Maskan'))

@section('content')
    {{-- Registration Form --}}
    <form id="registerForm" style="margin:0 auto">
        @csrf
        <input type="hidden" name="role" id="roleInput" value="owner">
        <div class="text-center mb-3">
            <span style="color:var(--gold); font-size:0.9rem;">{{ __('Property owner registration') }}</span>
        </div>

        <div class="mb-3">
            <label class="form-label">{{ __('Full name') }}</label>
            <input type="text" name="full_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">{{ __('Phone number') }}</label>
            <input type="text" name="phone_number" class="form-control" placeholder="0912345678" pattern="09[12348][0-9]{7}" maxlength="10" title="{{ __('Phone must start with 091, 092, 093, 094, or 098') }}" required>
        </div>
        <div class="row g-2">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">{{ __('Password') }}</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">{{ __('Confirm password') }}</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>
        </div>
        <div class="row g-2">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">{{ __('Date of birth') }}</label>
                    <input type="date" name="date_of_birth" class="form-control" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">{{ __('Gender') }}</label>
                    <select name="gender" class="form-select">
                        <option value="male">{{ __('Male') }}</option>
                        <option value="female">{{ __('Female') }}</option>
                    </select>
                </div>
            </div>
        </div>
        <div id="registerError" class="alert alert-danger d-none py-2 small"></div>
        <button type="submit" class="btn btn-maskan-primary d-flex justify-content-center align-items-center" id="registerBtn">{{ __('Create') }}</button>
    </form>

    {{-- OTP Verification Form --}}
    <form id="otpForm" style="margin:0 auto; display:none;">
        <input type="hidden" name="phone" id="otpPhone">
        <div class="text-center mb-3">
            <div style="font-size:2.5rem; color:var(--gold); margin-bottom:0.5rem;">✓</div>
            <h5 style="color:#fff; margin-bottom:0.25rem;">{{ __('Account created') }}</h5>
            <p style="color:rgba(255,255,255,0.6); font-size:0.85rem;">
                {{ __('Enter the verification code sent to') }} <strong id="otpPhoneDisplay" style="color:var(--gold);"></strong>
            </p>
        </div>
        <div class="mb-3">
            <label class="form-label">{{ __('Verification code') }}</label>
            <input type="text" name="otp" class="form-control text-center" maxlength="6" inputmode="numeric" autocomplete="one-time-code" style="font-size:1.5rem; letter-spacing:8px;" required>
        </div>
        <div id="otpError" class="alert alert-danger d-none py-2 small"></div>
        <button type="submit" class="btn btn-maskan-primary" id="otpBtn">{{ __('Confirm code') }}</button>
        <div class="text-center mt-2">
            <button type="button" id="resendOtpBtn" class="btn btn-link" style="color:var(--gold); font-size:0.85rem;">{{ __('Resend code') }}</button>
            <span id="resendTimer" style="color:rgba(255,255,255,0.4); font-size:0.85rem; display:none;"></span>
        </div>
    </form>

    <div class="auth-divider">{{ __('Or') }}</div>
    <div class="auth-links">
        <span>{{ __('Already have an account?') }}</span>
        <a href="{{ route('login') }}">{{ __('Login') }}</a>
    </div>

@push('scripts')
<script>
let registeredPhone = '';

document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('registerBtn');
    const errorDiv = document.getElementById('registerError');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __("Registering...") }}';
    errorDiv.classList.add('d-none');

    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    data.role = 'owner';

    try {
        const response = await fetch('{{ url('/api/register') }}', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();

        if (result.success) {
            localStorage.setItem('token', result.data.token);
            localStorage.setItem('user', JSON.stringify(result.data.user));
            registeredPhone = result.data.user.phone;
            showOtpForm(registeredPhone);
        } else {
            errorDiv.textContent = result.message || '{{ __("An error occurred") }}';
            errorDiv.classList.remove('d-none');
        }
    } catch (err) {
        errorDiv.textContent = '{{ __("An error occurred. Please try again.") }}';
        errorDiv.classList.remove('d-none');
    }

    btn.disabled = false;
    btn.innerHTML = '{{ __("Create") }}';
});

function showOtpForm(phone) {
    document.getElementById('registerForm').style.display = 'none';
    document.getElementById('otpForm').style.display = 'block';
    document.getElementById('otpPhone').value = phone;
    document.getElementById('otpPhoneDisplay').textContent = phone;
    startResendTimer();
}

document.getElementById('otpForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('otpBtn');
    const errorDiv = document.getElementById('otpError');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __("Verifying...") }}';
    errorDiv.classList.add('d-none');

    const data = {
        phone: document.getElementById('otpPhone').value,
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
            const user = JSON.parse(localStorage.getItem('user') || '{}');
            user.phone_verified_at = new Date().toISOString();
            localStorage.setItem('user', JSON.stringify(user));
            const userType = user.user_type;
            const dashboards = {
                admin: '{{ route("admin.dashboard") }}',
                owner: '{{ route("owner.dashboard") }}',
                tenant: '{{ route("dashboard") }}',
            };
            window.location.href = dashboards[userType] || '{{ route("dashboard") }}';
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

document.getElementById('resendOtpBtn').addEventListener('click', async function() {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __("Sending...") }}';

    try {
        await fetch('{{ url("/api/auth/send-otp") }}', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ phone: registeredPhone })
        });
        startResendTimer();
    } catch (err) {}

    btn.disabled = false;
    btn.innerHTML = '{{ __("Resend code") }}';
});

function startResendTimer() {
    const btn = document.getElementById('resendOtpBtn');
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

