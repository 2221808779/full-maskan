{{-- مسكن — صفحة عرض سجل المدفوعات --}}
@extends('layouts.app')

@section('title', __('Payments - Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ __('Payments') }}</h1>
</div>

<!-- Filter -->
<div class="table-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('pending') }}</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('completed') }}</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>{{ __('Failed') }}</option>
                    <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>{{ __('Refunded') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-maskan w-100">
                    <i class="fas fa-filter ms-1"></i> {{ __('Filter') }}
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
                    <th>{{ __('Property') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Payment Method') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Date') }}</th>
            </tr>
        </thead>
        <tbody>
            @php
            $typeLabels = ['deposit' => __('Deposit'), 'full' => __('Full'), 'partial' => __('Partial'), 'refund' => __('Refunded')];
            $statusLabels = ['pending' => __('pending'), 'completed' => __('completed'), 'failed' => __('Failed'), 'refunded' => __('Refunded')];
            @endphp
            @forelse($payments as $payment)
            <tr>
                <td>{{ $payment->id }}</td>
                <td>{{ $payment->booking->property->title ?? '—' }}</td>
                <td class="fw-bold" style="color: var(--gold);">{{ number_format($payment->amount, 2) }} {{ __('LYD') }}</td>
                <td>                    <span class="badge bg-secondary">{{ $typeLabels[$payment->payment_type] ?? __($payment->payment_type) }}</span></td>
                <td>{{ __($payment->payment_type) }}</td>
                <td><span class="badge badge-{{ $payment->status }}">{{ $statusLabels[$payment->status] ?? __($payment->status) }}</span></td>
                <td>{{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->format('Y-m-d') : ($payment->created_at->format('Y-m-d')) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-muted py-5">
                    <i class="fas fa-money-bill-wave fa-3x mb-3 d-block"></i>
                    {{ __('No payments found') }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="table-pagination">
        <span class="pagination-info">{{ __('Showing pagination', ['from' => $payments->firstItem() ?? 0, 'to' => $payments->lastItem() ?? 0, 'total' => $payments->total()]) }}</span>
        <div class="pagination-btns">
            {{ $payments->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
