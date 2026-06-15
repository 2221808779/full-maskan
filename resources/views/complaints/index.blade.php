{{-- مسكن — صفحة عرض قائمة الشكاوى --}}
@extends('layouts.app')

@section('title', __('Complaints - Maskan'))

@section('content')
<div class="page-header">
    <h1>{{ __('Complaints') }}</h1>
    @if(auth()->user()->user_type !== 'admin')
    <a href="{{ route('complaints.create') }}" class="btn btn-maskan">
        <i class="fas fa-plus-circle ms-2"></i> {{ __('New Complaint') }}
    </a>
    @endif
</div>

<div class="table-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    @if(auth()->user()->user_type === 'admin')
                    <th>{{ __('Sender') }}</th>
                    @endif
                    <th>{{ __('Complaint') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @php $statusMap = ['pending' => 'warning', 'in_review' => 'info', 'resolved' => 'success', 'dismissed' => 'secondary'] @endphp
                @forelse($complaints as $complaint)
                <tr>
                    <td>{{ $complaint->id }}</td>
                    @if(auth()->user()->user_type === 'admin')
                    <td>{{ $complaint->sender->full_name ?? '—' }}</td>
                    @endif
                    <td>
                        <a href="{{ route('complaints.show', $complaint) }}" class="fw-bold text-decoration-none" style="color: var(--blue-dark);">
                            {{ Str::limit($complaint->message_text, 60) }}
                        </a>
                    </td>
                    <td>
                        <span class="badge badge-{{ $statusMap[$complaint->complaint_status] ?? 'secondary' }}">{{ __($complaint->complaint_status) }}</span>
                    </td>
                    <td>{{ $complaint->sent_at?->format('Y-m-d') ?? $complaint->created_at->format('Y-m-d') }}</td>
                    <td>
                        <a href="{{ route('complaints.show', $complaint) }}" class="action-btn" title="{{ __('View') }}">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ auth()->user()->user_type === 'admin' ? '6' : '5' }}" class="text-center text-muted py-5">
                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                        {{ __('No complaints currently') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    <div class="table-pagination">
        <span class="pagination-info">{{ __('Showing pagination', ['from' => $complaints->firstItem() ?? 0, 'to' => $complaints->lastItem() ?? 0, 'total' => $complaints->total()]) }}</span>
        <div class="pagination-btns">
            {{ $complaints->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
