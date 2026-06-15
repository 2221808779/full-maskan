{{-- مسكن — صفحة تعديل بيانات مستخدم من لوحة المسؤول --}}
@extends('layouts.app')

@section('title', __('Edit User - Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ __('Edit User') }}: {{ $user->full_name }}</h1>
    <a href="{{ route('admin.users') }}" class="btn btn-outline-gold">
        <i class="fas fa-arrow-right ms-1"></i> {{ __('Back') }}
    </a>
</div>

<div class="maskan-card">
    <div class="card-body">
        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="form-maskan">
            @csrf @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Full Name') }}</label>
                    <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror"
                           value="{{ old('full_name', $user->full_name) }}" required>
                    @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Role') }}</label>
                    <select name="user_type" class="form-select @error('user_type') is-invalid @enderror" required>
                        <option value="owner" {{ $user->user_type === 'owner' ? 'selected' : '' }}>{{ __('Property Owner') }}</option>
                        <option value="tenant" {{ $user->user_type === 'tenant' ? 'selected' : '' }}>{{ __('Tenant') }}</option>
                        <option value="technician" {{ $user->user_type === 'technician' ? 'selected' : '' }}>{{ __('Technician') }}</option>
                    </select>
                    @error('user_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Phone') }}</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone', $user->phone) }}" pattern="09[12348][0-9]{7}" maxlength="10" title="{{ __('Phone must start with 091, 092, 093, 094, or 098') }}" required>
                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('New Password') }} ({{ __('Leave empty if no change') }})</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                           placeholder="{{ __('New Password') }}">
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Confirm Password') }}</label>
                    <input type="password" name="password_confirmation" class="form-control"
                           placeholder="{{ __('Confirm Password') }}">
                </div>
            </div>

            {{-- Specializations & Experience for Technician --}}
            <div id="technicianFields" style="display: {{ $user->user_type === 'technician' ? 'block' : 'none' }};">
                <hr>
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label">{{ __('Specializations') }} <span class="text-danger">*</span></label>
                        <div class="row">
                            @php $userSpecs = $user->user_type === 'technician' && $user->technicianProfile ? $user->technicianProfile->specializations->pluck('id')->toArray() : []; @endphp
                            @foreach($specialties as $spec)
                            <div class="col-md-4 col-sm-6 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="specializations[]"
                                           value="{{ $spec->id }}" id="spec_{{ $spec->id }}"
                                           {{ in_array($spec->id, old('specializations', $userSpecs)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="spec_{{ $spec->id }}">
                                        {{ __($spec->name) }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @error('specializations') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('Experience Years') }}</label>
                        <input type="number" name="experience_years" class="form-control"
                               min="0" max="70" value="{{ old('experience_years', $user->user_type === 'technician' && $user->technicianProfile ? $user->technicianProfile->experience_years : '') }}">
                    </div>
                </div>
            </div>

            <hr>
            <button type="submit" class="btn btn-maskan px-4">
                <i class="fas fa-save ms-1"></i> {{ __('Save Changes') }}
            </button>
        </form>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.querySelector('select[name="user_type"]');
    const techFields = document.getElementById('technicianFields');
    function toggleFields() {
        techFields.style.display = select.value === 'technician' ? 'block' : 'none';
    }
    select.addEventListener('change', toggleFields);
});
</script>
@endpush
@endsection
