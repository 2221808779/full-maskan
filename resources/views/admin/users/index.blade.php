{{-- مسكن — صفحة إدارة المستخدمين للمسؤول --}}
@extends('layouts.app')

@section('title', __('Users Management - Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ __('Users Management') }}</h1>
    <a href="{{ route('admin.users.create') }}" class="btn btn-maskan">
        <i class="fas fa-user-plus ms-1"></i> {{ __('Add User') }}
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}</div>
@endif
@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show">{{ session('info') }}</div>
@endif

<div class="table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="{{ __('Search user...') }}"
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="user_type" class="form-select">
                    <option value="">{{ __('All Roles') }}</option>
                    <option value="owner" {{ request('user_type') == 'owner' ? 'selected' : '' }}>{{ __('Owner') }}</option>
                    <option value="tenant" {{ request('user_type') == 'tenant' ? 'selected' : '' }}>{{ __('Tenant') }}</option>
                    <option value="technician" {{ request('user_type') == 'technician' ? 'selected' : '' }}>{{ __('Technician') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>{{ __('Suspended') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-maskan w-100">
                    <i class="fas fa-search ms-1"></i> {{ __('Search') }}
                </button>
            </div>
        </form>
    </div>
</div>

<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Phone') }}</th>
                <th>{{ __('Role') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Registration Date') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--blue); color: white; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                            <i class="fas fa-user"></i>
                        </div>
                        <strong>{{ $user->full_name }}</strong>
                    </div>
                </td>
                <td dir="ltr">{{ $user->phone ?? '—' }}</td>
                <td>
                    @php $roles = ['admin' => __('Admin'), 'owner' => __('Owner'), 'tenant' => __('Tenant'), 'technician' => __('Technician')] @endphp
                    <span class="badge" style="background: rgba(45,95,138,0.15); color: var(--blue);">
                        {{ $roles[$user->user_type] ?? $user->user_type ?? '—' }}
                    </span>
                </td>
                <td>
                    @if($user->status === 'suspended')
                        <span class="badge bg-danger">{{ __('Suspended') }}</span>
                    @elseif($user->status === 'inactive')
                        <span class="badge bg-warning">{{ __('Inactive') }}</span>
                    @else
                        <span class="badge bg-success">{{ __('Active') }}</span>
                    @endif
                </td>
                <td>{{ $user->created_at->format('Y-m-d') }}</td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="{{ route('admin.users.edit', $user) }}" class="action-btn" title="{{ __('Edit') }}">
                            <i class="fas fa-edit"></i>
                        </a>
                        @if($user->user_type !== 'admin')
                            @if($user->status === 'suspended')
                                <a href="{{ route('admin.users.unban', $user) }}" class="action-btn success"
                                   onclick="return confirm('{{ __('Unban user?') }}')" title="{{ __('Unban') }}">
                                    <i class="fas fa-check-circle"></i>
                                </a>
                            @else
                                <button type="button" class="action-btn danger" title="{{ __('Ban') }}"
                                        data-bs-toggle="modal" data-bs-target="#banModal{{ $user->id }}">
                                    <i class="fas fa-ban"></i>
                                </button>
                            @endif
                            <button type="button" class="action-btn danger" title="{{ __('Delete') }}"
                                    data-bs-toggle="modal" data-bs-target="#deleteModal{{ $user->id }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
            {{-- Ban Modal --}}
            <div class="modal fade" id="banModal{{ $user->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('admin.users.ban', $user) }}" method="POST" onsubmit="return validateBanForm(this)">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">{{ __('Ban user') }}: {{ $user->full_name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Ban reason') }} <span class="text-danger">*</span></label>
                                        <select name="reason_type" class="form-select mb-2" onchange="toggleCustomReason(this)">
                                            <option value="">{{ __('Select a reason...') }}</option>
                                            <option value="{{ __('Inappropriate behavior') }}">{{ __('Inappropriate behavior') }}</option>
                                            <option value="{{ __('Repeated complaints') }}">{{ __('Repeated complaints') }}</option>
                                            <option value="{{ __('Fake data') }}">{{ __('Fake data') }}</option>
                                            <option value="{{ __('Terms violation') }}">{{ __('Terms violation') }}</option>
                                            <option value="custom">{{ __('Other (manual)') }}</option>
                                        </select>
                                    <input type="text" name="reason" class="form-control d-none" id="customReason{{ $user->id }}" placeholder="{{ __('Enter custom reason...') }}">
                                    <div class="invalid-feedback">{{ __('Please enter ban reason') }}</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Additional details') }} ({{ __('optional') }})</label>
                                    <textarea name="details" class="form-control" rows="2" placeholder="{{ __('Duration, conditions for lifting ban, etc.') }}"></textarea>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="ban_type" id="permBan{{ $user->id }}" value="permanent" checked>
                                        <label class="form-check-label" for="permBan{{ $user->id }}">{{ __('Permanent') }}</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="ban_type" id="tempBan{{ $user->id }}" value="temporary">
                                        <label class="form-check-label" for="tempBan{{ $user->id }}">{{ __('Temporary') }}</label>
                                    </div>
                                </div>
                                <div class="mb-3 d-none" id="banUntilGroup{{ $user->id }}">
                                    <label class="form-label">{{ __('Ban until') }}</label>
                                    <input type="date" name="banned_until" class="form-control">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                <button type="submit" class="btn btn-danger">{{ __('Ban & Send Notification') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            {{-- Delete Modal --}}
            <div class="modal fade" id="deleteModal{{ $user->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">{{ __('Delete user') }}: {{ $user->full_name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle ms-1"></i>
                                    {{ __('This action cannot be undone. The system will check for active bookings and pending payments before deleting.') }}
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                <button type="submit" class="btn btn-danger">{{ __('Delete permanently') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <tr>
                <td colspan="7" class="text-center text-muted py-5">
                    <i class="fas fa-users-slash fa-3x mb-3 d-block"></i>
                    {{ __('No users found') }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="table-pagination">
        <span class="pagination-info">{{ __('Showing pagination', ['from' => $users->firstItem() ?? 0, 'to' => $users->lastItem() ?? 0, 'total' => $users->total()]) }}</span>
        <div class="pagination-btns">
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleCustomReason(select) {
    var customInput = select.parentElement.querySelector('input[name="reason"]');
    if (select.value === 'custom') {
        customInput.classList.remove('d-none');
        customInput.setAttribute('required', '');
    } else {
        customInput.classList.add('d-none');
        customInput.removeAttribute('required');
    }
}

function validateBanForm(form) {
    var reasonType = form.querySelector('select[name="reason_type"]').value;
    var customReason = form.querySelector('input[name="reason"]').value;
    if (!reasonType || (reasonType === 'custom' && !customReason.trim())) {
        alert('{{ __('Please enter ban reason') }}');
        return false;
    }
    if (reasonType !== 'custom') {
        form.querySelector('input[name="reason"]').value = reasonType;
    }
    return true;
}

document.querySelectorAll('input[name="ban_type"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        var modal = this.closest('.modal');
        var untilGroup = modal.querySelector('[id^="banUntilGroup"]');
        if (this.value === 'temporary') {
            untilGroup.classList.remove('d-none');
        } else {
            untilGroup.classList.add('d-none');
        }
    });
});
</script>
@endpush
