{{-- مسكن — صفحة الملف الشخصي للمستخدم --}}
@extends('layouts.app')

@section('title', __('Profile - Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ __('Profile') }}</h1>
</div>

{{-- Profile Info --}}
<div class="maskan-card mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 70px; height: 70px; border-radius: 50%; background: linear-gradient(135deg, var(--blue), #1a3a5c); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; flex-shrink: 0; overflow: hidden;">
                @if(auth()->user()->profile_image)
                    <img src="{{ Storage::url(auth()->user()->profile_image) }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                @else
                    <i class="fas fa-user"></i>
                @endif
            </div>
            <div>
                <h5 class="fw-bold mb-1">{{ auth()->user()->full_name }}</h5>
                @php $roles = ['admin' => __('Admin'), 'owner' => __('Property Owner'), 'tenant' => __('Tenant'), 'technician' => __('Technician')] @endphp
                <span class="badge" style="background:rgba(212,175,55,0.12); color:#b8960f; font-size:0.75rem; padding:3px 12px; border-radius:20px;">
                    {{ $roles[auth()->user()->user_type] ?? auth()->user()->user_type ?? '—' }}
                </span>
                <div class="mt-1" style="font-size:0.85rem; color:var(--gray-500);">
                    <span><i class="fas fa-phone ms-1"></i>{{ auth()->user()->phone ?? '—' }}</span>
                </div>
                @if(in_array(auth()->user()->user_type, ['owner', 'technician']) && auth()->user()->reviews->count() > 0)
                    <div class="mt-1" style="font-size:0.85rem;">
                        <i class="fas fa-star" style="color:var(--gold);"></i>
                        {{ number_format(auth()->user()->reviews->avg('stars'), 1) }}
                        ({{ auth()->user()->reviews->count() }} {{ __('reviews') }})
                    </div>
                @endif
            </div>
        </div>
        @php
            $incomplete = [];
            if (!auth()->user()->profile_image) $incomplete[] = __('profile image');
            if (!auth()->user()->phone) $incomplete[] = __('phone number');
        @endphp
        @if(count($incomplete) > 0)
            <div class="alert alert-warning mt-3 mb-0 py-2 small">
                <i class="fas fa-exclamation-triangle ms-1"></i>
                {{ __('Your profile is incomplete. Please add') }}: {{ implode(', ', $incomplete) }}
            </div>
        @endif
    </div>
</div>

{{-- Edit Profile --}}
<div class="maskan-card mb-4">
    <div class="card-header">
         {{ __('Edit Profile') }}
    </div>
    <div class="card-body">
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Name') }}</label>
                    <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror"
                           value="{{ old('full_name', auth()->user()->full_name) }}" required>
                    @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Phone') }}</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone', auth()->user()->phone) }}" pattern="09[12348][0-9]{7}" maxlength="10" title="{{ __('Phone must start with 091, 092, 093, 094, or 098') }}" required>
                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Profile Image') }}</label>
                    <input type="file" name="profile_image" class="form-control @error('profile_image') is-invalid @enderror" accept="image/jpeg,image/png,image/gif">
                    <small class="text-muted">{{ __('Max 2MB. JPG, PNG, GIF only.') }}</small>
                    @error('profile_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <button type="submit" class="btn btn-maskan">
                <i class="fas fa-save ms-1"></i> {{ __('Save') }}
            </button>
        </form>
    </div>
</div>

@if(auth()->user()->user_type === 'technician')
@php $techProfile = auth()->user()->technicianProfile; @endphp
<div class="maskan-card mb-4">
    <div class="card-header">
         {{ __('Technician Profile') }}
    </div>
    <div class="card-body">
        <form action="{{ route('profile.update') }}" method="POST">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Years of experience') }}</label>
                    <input type="number" name="experience_years" class="form-control" min="0" max="70"
                           value="{{ old('experience_years', $techProfile->experience_years ?? '') }}" placeholder="{{ __('Years') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Bio') }}</label>
                    <textarea name="bio" class="form-control" rows="2" maxlength="500" placeholder="{{ __('Brief description about yourself') }}">{{ old('bio', $techProfile->bio ?? '') }}</textarea>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('Specializations') }}</label>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($specialties as $spec)
                        @php $checked = $techProfile && $techProfile->specializations->contains($spec->id); @endphp
                        <label class="btn btn-outline-secondary btn-sm" style="cursor:pointer;{{ $checked ? 'border-color:var(--gold);background:var(--gold-pale);' : '' }}">
                            <input type="checkbox" name="specializations[]" value="{{ $spec->id }}"
                                   onchange="this.parentElement.style.borderColor=this.checked?'var(--gold)':'var(--gray-200)';this.parentElement.style.background=this.checked?'var(--gold-pale)':'transparent';"
                                   {{ $checked ? 'checked' : '' }} style="display:none;">
                            {{ $spec->name }}
                        </label>
                    @endforeach
                </div>
            </div>
            <button type="submit" class="btn btn-maskan">
                <i class="fas fa-save ms-1"></i> {{ __('Save') }}
            </button>
        </form>
    </div>
</div>
@endif

{{-- Change Password --}}
<div class="maskan-card mb-4">
    <div class="card-header">
         {{ __('Change Password') }}
    </div>
    <div class="card-body">
        <form action="{{ route('profile.password') }}" method="POST">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('Current Password') }}</label>
                    <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                    @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('New Password') }}</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('Confirm Password') }}</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>
            <button type="submit" class="btn btn-gold">
                <i class="fas fa-key ms-1"></i> {{ __('Change Password') }}
            </button>
        </form>
    </div>
</div>

{{-- Danger Zone --}}
<div class="maskan-card mb-5" style="border-color: var(--danger) !important;">
    <div class="card-header" style="color: var(--danger);">
        <i class="fas fa-exclamation-triangle ms-1"></i> {{ __('Danger Zone') }}
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3 mb-md-0">
                <h6 style="font-weight: 600; color: var(--gray-800);">{{ __('Deactivate Account') }}</h6>
                <p style="font-size: 0.85rem; color: var(--gray-500); margin-bottom: 10px;">
                    {{ __('Deactivate your account temporarily') }}
                </p>
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#deactivateModal">
                    <i class="fas fa-pause-circle ms-1"></i> {{ __('Deactivate Account') }}
                </button>
            </div>
            <div class="col-md-6">
                <h6 style="font-weight: 600; color: var(--danger);">{{ __('Delete Account Permanently') }}</h6>
                <p style="font-size: 0.85rem; color: var(--gray-500); margin-bottom: 10px;">
                    {{ __('Permanently delete your account and all data') }}
                </p>
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="fas fa-trash ms-1"></i> {{ __('Delete Account') }}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Deactivate Modal --}}
<div class="modal fade" id="deactivateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('profile.deactivate') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Deactivate Account') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle ms-1"></i>
                        {{ __('Your properties and profile will be hidden. You can reactivate by logging in again.') }}
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Enter your password to confirm') }}</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-warning">{{ __('Deactivate') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('profile.destroy') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Delete Account Permanently') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle ms-1"></i>
                        <strong>{{ __('Warning: This cannot be undone!') }}</strong><br>
                        {{ __('All your data including properties, bookings, messages, and reviews will be permanently deleted.') }}
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Enter your password to confirm') }}</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('Delete Permanently') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div style="height: 2rem;"></div>
@endsection
