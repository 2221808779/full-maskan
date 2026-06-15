{{-- مسكن — صفحة عرض تفاصيل طلب الصيانة --}}
@extends('layouts.app')

@section('title', __('Maintenance Request - Maskan'))

@php
$priColors = ['low' => 'secondary', 'medium' => 'info', 'high' => 'warning', 'urgent' => 'danger'];
$statusLabels = [
    'pending' => __('Pending'),
    'assigned' => __('Assigned'),
    'in_progress' => __('In Progress'),
    'completed' => __('Completed'),
    'cancelled' => __('Cancelled'),
];
@endphp

@section('content')
<div class="page-header">
    <h1>{{ __('Maintenance Request') }} {{ $maintenanceRequest->id }}</h1>
    <a href="{{ route('maintenance.index') }}" class="btn btn-outline-gold">
        <i class="fas fa-arrow-right ms-1"></i> {{ __('Back') }}
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="maskan-card mb-4">
            <div class="card-header-custom d-flex align-items-center justify-content-between px-4 py-3" style="border-bottom:1px solid var(--gray-100);">
                <h5 class="mb-0">{{ __('Request Details') }}</h5>
                <span class="badge badge-{{ $maintenanceRequest->status }} fs-6 px-3 py-2">{{ __($statusLabels[$maintenanceRequest->status] ?? $maintenanceRequest->status) }}</span>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="d-flex flex-column gap-3">
                            <div>
                                <small class="text-muted d-block mb-1">{{ __('Property') }}</small>
                                <a href="{{ route('properties.show', $maintenanceRequest->property) }}" class="fw-semibold" style="color:var(--blue);">
                                    <i class="fas fa-building ms-2"></i>{{ $maintenanceRequest->property->title ?? '—' }}
                                </a>
                            </div>
                            <div>
                                <small class="text-muted d-block mb-1">{{ __('Tenant') }}</small>
                                <span class="fw-semibold"><i class="fas fa-user ms-2"></i>{{ $maintenanceRequest->tenant->full_name ?? '—' }}</span>
                            </div>
                            <div>
                                <small class="text-muted d-block mb-1">{{ __('Category') }}</small>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="badge bg-secondary">{{ __($maintenanceRequest->ai_category ?? $maintenanceRequest->category ?? '—') }}</span>
                                    @if($maintenanceRequest->ai_category)
                                        <span class="badge bg-info"><i class="fas fa-robot ms-1"></i> {{ __('AI') }}</span>
                                    @endif
                                    @if($maintenanceRequest->ai_accuracy)
                                        <span class="badge bg-info">{{ __('Accuracy') }}: {{ number_format($maintenanceRequest->ai_accuracy * 100, 0) }}%</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex flex-column gap-3">
                            <div>
                                <small class="text-muted d-block mb-1">{{ __('Priority') }}</small>
                                <span class="badge bg-{{ $priColors[$maintenanceRequest->priority] ?? 'secondary' }} fs-6 px-3 py-1">{{ __($maintenanceRequest->priority ?? '—') }}</span>
                            </div>
                            <div>
                                <small class="text-muted d-block mb-1">{{ __('Status') }}</small>
                                <span class="fw-semibold">{{ $statusLabels[$maintenanceRequest->status] ?? $maintenanceRequest->status }}</span>
                            </div>
                            @if($maintenanceRequest->completed_at)
                            <div>
                                <small class="text-muted d-block mb-1">{{ __('Completed At') }}</small>
                                <span class="fw-semibold">{{ \Carbon\Carbon::parse($maintenanceRequest->completed_at)->translatedFormat('d F Y, h:i A') }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div>
                    <h6 class="fw-bold mb-3"><i class="fas fa-align-left ms-2 gold-text"></i>{{ __('Description') }}</h6>
                    <p class="mb-0 lh-lg" style="white-space:pre-wrap;background:var(--gray-50);padding:16px;border-radius:8px;">{{ $maintenanceRequest->problem_description }}</p>
                </div>

                @if($maintenanceRequest->technician_notes)
                <div class="mt-4">
                    <h6 class="fw-bold mb-3"><i class="fas fa-sticky-note ms-2 gold-text"></i>{{ __('Technician Notes') }}</h6>
                    <p class="mb-0 lh-lg" style="white-space:pre-wrap;background:var(--gray-50);padding:16px;border-radius:8px;">{{ $maintenanceRequest->technician_notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        @if(auth()->user()->user_type === 'owner' && in_array($maintenanceRequest->status, ['pending']))
        <div class="maskan-card mb-4">
            <div class="card-header-custom d-flex align-items-center px-4 py-3" style="border-bottom:1px solid var(--gray-100);">
                <h5 class="mb-0">{{ __('Assign Technician') }}</h5>
            </div>
            <div class="card-body">
                @php $category = $maintenanceRequest->ai_category ?? $maintenanceRequest->category; @endphp
                @if($category)
                    <div class="alert alert-info p-3 mb-3 rounded-3 d-flex align-items-center gap-2">
                        <i class="fas fa-robot gold-text"></i>
                        <span>{{ __('AI recommends') }}: <strong>{{ __($category) }}</strong>
                        @if($maintenanceRequest->ai_accuracy)
                            ({{ number_format($maintenanceRequest->ai_accuracy * 100, 0) }}%)
                        @endif
                        </span>
                    </div>
                @endif

                @php $activePred = $maintenanceRequest->relationLoaded('property') && $maintenanceRequest->property->relationLoaded('activePrediction') ? $maintenanceRequest->property->activePrediction : null; @endphp
                @if($activePred && (auth()->user()->user_type === 'owner' || auth()->user()->user_type === 'admin'))
                <div class="alert alert-warning p-3 mb-3 rounded-3 d-flex align-items-center gap-2" style="background:rgba(212,175,55,0.08);border-color:rgba(212,175,55,0.2);">
                    <i class="fas fa-robot gold-text"></i>
                    <div>
                        <strong>{{ __('AI predicts next maintenance') }}</strong>
                        <span class="badge ms-1" style="background:rgba(212,175,55,0.12);color:#b8960f;">{{ __($activePred->predicted_category) }}</span>
                        <div class="small mt-1">
                            {{ $activePred->predicted_date->translatedFormat('d F Y') }}
                            ({{ __('in :days days', ['days' => $activePred->days_until_next]) }})
                        </div>
                    </div>
                </div>
                @endif
                <form action="{{ route('maintenance.assign', $maintenanceRequest) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <small class="text-muted d-block mb-2">{{ __('Filtered by') }}: <strong>{{ __($category) }}</strong></small>
                        <select name="technician_id" class="form-select" required>
                            <option value="">{{ __('Select Technician') }}</option>
                            @foreach($technicians as $tech)
                                @php $tp = $tech->technicianProfile; @endphp
                                <option value="{{ $tech->id }}">
                                    {{ $tech->full_name }}
                                    @if($tp)
                                        @if($tp->experience_years)
                                            ({{ $tp->experience_years }} {{ __('Years') }})
                                        @endif
                                        @if($tp->specializations->isNotEmpty())
                                            — {{ $tp->specializations->pluck('name')->implode(', ') }}
                                        @endif
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-maskan w-100">
                        <i class="fas fa-check ms-1"></i> {{ __('Assign') }}
                    </button>
                </form>
            </div>
        </div>
        @endif

        @if($maintenanceRequest->technician)
        <div class="maskan-card mb-4">
            <div class="card-header-custom d-flex align-items-center px-4 py-3" style="border-bottom:1px solid var(--gray-100);">
                <h5 class="mb-0">{{ __('Assigned Technician') }}</h5>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-user-circle fa-5x" style="color:var(--gold);"></i>
                </div>
                <h5 class="fw-bold">{{ $maintenanceRequest->technician->full_name }}</h5>
                @php $techProfile = $maintenanceRequest->technician->technicianProfile; @endphp
                @if($techProfile)
                    <div class="mt-2" style="color:var(--gray-600);font-size:0.9rem;">
                        @if($techProfile->experience_years)
                            <span class="d-block"><i class="fas fa-briefcase ms-1"></i> {{ $techProfile->experience_years }} {{ __('Years') }}</span>
                        @endif
                        @if($techProfile->specializations->isNotEmpty())
                            @foreach($techProfile->specializations as $spec)
                                <span class="badge me-1" style="background:rgba(212,175,55,0.12);color:#b8960f;font-size:0.75rem;">{{ $spec->name }}</span>
                            @endforeach
                        @endif
                        @if($techProfile->bio)
                            <div class="mt-2 small">{{ $techProfile->bio }}</div>
                        @endif
                    </div>
                    @if($techProfile->reviews_count > 0)
                    <div class="mt-2 mb-2">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star{{ $i <= round($techProfile->avg_rating) ? '' : '-o' }}" style="color:#f1c40f;font-size:14px;"></i>
                        @endfor
                        <div><small class="text-muted">{{ number_format($techProfile->avg_rating, 1) }} ({{ $techProfile->reviews_count }} {{ __('reviews') }})</small></div>
                    </div>
                @endif
            @endif
            </div>
        </div>
        @endif

        @if(auth()->user()->user_type === 'owner' && isset($review) && $review)
        <div class="maskan-card mb-4">
            <div class="card-header-custom d-flex align-items-center px-4 py-3" style="border-bottom:1px solid var(--gray-100);">
                <h5 class="mb-0">{{ __('Technician Rating') }}</h5>
            </div>
            <div class="card-body text-center">
                <div class="mb-2" style="font-size:1.8rem;">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= $review->stars)
                            <i class="fas fa-star" style="color:var(--gold);"></i>
                        @else
                            <i class="far fa-star" style="color:var(--gray-400);"></i>
                        @endif
                    @endfor
                </div>
                <div class="fw-bold fs-4" style="color:var(--gold);">{{ $review->stars }}/5</div>
                @if($review->comment)
                    <p class="mt-3 mb-0 p-3 rounded-3 text-start lh-base" style="background:var(--gray-50);font-size:14px;">
                        {{ $review->comment }}
                    </p>
                @endif
                @if($review->user)
                    <small class="text-muted d-block mt-2">{{ __('By') }} {{ $review->user->full_name }}</small>
                @endif
            </div>
        </div>
        @endif

        @if(auth()->user()->user_type === 'technician' && $maintenanceRequest->technician_id === auth()->id() && !in_array($maintenanceRequest->status, ['completed','cancelled']))
        <div class="maskan-card mb-4">
            <div class="card-header-custom d-flex align-items-center px-4 py-3" style="border-bottom:1px solid var(--gray-100);">
                <h5 class="mb-0">{{ __('Update Status') }}</h5>
            </div>
            <div class="card-body">
                @if($maintenanceRequest->status === 'assigned')
                <form action="{{ route('maintenance.status', $maintenanceRequest) }}" method="POST" class="mb-3">
                    @csrf
                    <input type="hidden" name="status" value="in_progress">
                    <div class="mb-3">
                        <textarea name="technician_notes" class="form-control" rows="2" placeholder="{{ __('Add notes') }}">{{ old('technician_notes') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-maskan w-100 mb-2">
                        <i class="fas fa-play ms-1"></i> {{ __('Start Work') }}
                    </button>
                </form>
                <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="fas fa-times ms-1"></i> {{ __('Reject Request') }}
                </button>
                @elseif($maintenanceRequest->status === 'in_progress')
                <form action="{{ route('maintenance.status', $maintenanceRequest) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="completed">
                    <div class="mb-3">
                        <textarea name="technician_notes" class="form-control" rows="3" placeholder="{{ __('Add notes') }}">{{ old('technician_notes') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-maskan w-100">
                        <i class="fas fa-check ms-1"></i> {{ __('Complete') }}
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endif

        @if(auth()->user()->user_type === 'owner' && $maintenanceRequest->status === 'assigned')
        <div class="maskan-card mb-4">
            <div class="card-header-custom d-flex align-items-center px-4 py-3" style="border-bottom:1px solid var(--gray-100);">
                <h5 class="mb-0">{{ __('Cancel Request') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('maintenance.status', $maintenanceRequest) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to cancel this request?') }}')">
                    @csrf
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="fas fa-ban ms-1"></i> {{ __('Cancel Request') }}
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Reject Modal --}}
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('maintenance.reject', $maintenanceRequest) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="rejectModalLabel">{{ __('Reject Request') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="rejectReason" class="form-label">{{ __('Reason for rejection') }} <span class="text-danger">*</span></label>
                                <textarea name="reason" id="rejectReason" class="form-control" rows="4" required placeholder="{{ __('Explain why you cannot handle this request') }}"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-times ms-1"></i> {{ __('Reject') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if(auth()->user()->user_type === 'tenant' && $maintenanceRequest->status === 'completed' && $maintenanceRequest->tenant_id === auth()->id())
        <div class="maskan-card mb-4">
            <div class="card-header-custom d-flex align-items-center px-4 py-3" style="border-bottom:1px solid var(--gray-100);">
                <h5 class="mb-0">{{ __('Rate Technician') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('maintenance.rate', $maintenanceRequest) }}" method="POST">
                    @csrf
                    <div class="mb-3 text-center">
                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            @for($i = 5; $i >= 1; $i--)
                                <div class="text-center">
                                    <input type="radio" name="rating" id="star{{ $i }}" value="{{ $i }}" class="btn-check"{{ $i == 5 ? ' checked' : '' }}>
                                    <label for="star{{ $i }}" class="btn btn-outline-warning px-3 py-2">
                                        <i class="fas fa-star"></i> {{ $i }}
                                    </label>
                                </div>
                            @endfor
                        </div>
                    </div>
                    <button type="submit" class="btn btn-maskan w-100">
                        <i class="fas fa-paper-plane ms-1"></i> {{ __('Submit Rating') }}
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
