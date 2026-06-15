{{-- مسكن — صفحة عرض تفاصيل الشكوى --}}
@extends('layouts.app')

@section('title', __('Complaint - Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ __('Complaint') }} {{ $complaint->id }}</h1>
    <a href="{{ route('complaints.index') }}" class="btn btn-outline-gold">
        <i class="fas fa-arrow-right ms-2"></i> {{ __('Back') }}
    </a>
</div>

@php $statusMap = ['pending' => 'warning', 'in_review' => 'info', 'resolved' => 'success', 'dismissed' => 'secondary'] @endphp

<div class="row g-5">
    <div class="col-lg-7">
        <div class="maskan-card mb-4">
            <div class="card-header-custom">
                <h5>{{ __('Complaint Details') }}</h5>
                <span class="badge badge-{{ $statusMap[$complaint->complaint_status] ?? 'secondary' }} px-3 py-2">{{ __($complaint->complaint_status) }}</span>
            </div>
            <div class="card-body">
                <table class="info-table">
                    <tr>
                        <th class="pb-3">{{ __('Sender') }}</th>
                        <td class="pb-3">{{ $complaint->sender->full_name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th class="pb-3">{{ __('Message') }}</th>
                        <td class="pb-3" style="white-space: pre-wrap;">{{ $complaint->message_text }}</td>
                    </tr>
                    <tr>
                        <th class="pb-3">{{ __('Status') }}</th>
                        <td class="pb-3"><span class="badge badge-{{ $statusMap[$complaint->complaint_status] ?? 'secondary' }}">{{ __($complaint->complaint_status) }}</span></td>
                    </tr>
                    <tr>
                        <th class="pb-3">{{ __('Sent At') }}</th>
                        <td class="pb-3">{{ $complaint->sent_at?->translatedFormat('d F Y, h:i A') ?? $complaint->created_at->translatedFormat('d F Y, h:i A') }}</td>
                    </tr>
                    @if($complaint->resolved_at)
                    <tr>
                        <th class="pb-3">{{ __('Resolved At') }}</th>
                        <td class="pb-3">{{ $complaint->resolved_at->translatedFormat('d F Y, h:i A') }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        @if($complaint->admin_response)
        <div class="maskan-card mb-4">
            <div class="card-header-custom">
                <h5>{{ __('Admin Response') }}</h5>
            </div>
            <div class="card-body">
                <p style="white-space: pre-wrap; line-height: 1.8;">{{ $complaint->admin_response }}</p>
                @if($complaint->responder)
                <small class="text-muted">{{ __('Responded by') }}: {{ $complaint->responder->full_name }}</small>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-5">
        @if(auth()->user()->user_type === 'admin')
        <div class="maskan-card mb-4">
            <div class="card-header-custom">
                <h5>{{ __('Change Status') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('complaints.status', $complaint) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <select name="complaint_status" class="form-select">
                            <option value="pending" {{ $complaint->complaint_status === 'pending' ? 'selected' : '' }}>{{ __('pending') }}</option>
                            <option value="in_review" {{ $complaint->complaint_status === 'in_review' ? 'selected' : '' }}>{{ __('in_review') }}</option>
                            <option value="resolved" {{ $complaint->complaint_status === 'resolved' ? 'selected' : '' }}>{{ __('resolved') }}</option>
                            <option value="dismissed" {{ $complaint->complaint_status === 'dismissed' ? 'selected' : '' }}>{{ __('dismissed') }}</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-maskan w-100 py-2">
                        <i class="fas fa-sync-alt ms-2"></i> {{ __('Update Status') }}
                    </button>
                </form>
            </div>
        </div>

        @if($complaint->complaint_status !== 'resolved' && $complaint->complaint_status !== 'dismissed')
        <div class="maskan-card mb-4">
            <div class="card-header-custom">
                <h5>{{ __('Respond') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('complaints.respond', $complaint) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <textarea name="admin_response" class="form-control" rows="5"
                                  placeholder="{{ __('Write your response...') }}" required>{{ old('admin_response') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-maskan w-100 py-2">
                        <i class="fas fa-paper-plane ms-2"></i> {{ __('Submit Response') }}
                    </button>
                </form>
            </div>
        </div>
        @endif
        @endif
    </div>
</div>
@endsection
