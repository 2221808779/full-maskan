{{-- مسكن — صفحة التقويم والتوفر للمالك --}}
@extends('layouts.app')

@section('title', __('Calendar & Availability') . ' - ' . __('Maskan'))

@php $ar = app()->getLocale() === 'ar'; @endphp

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
@if($ar)
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/plugins/confirmDate/confirmDate.css">
<style>
.flatpickr-calendar { direction: rtl; }
.flatpickr-prev-month, .flatpickr-next-month { transform: scaleX(-1); }
.flatpickr-day.booked-date { background: var(--gold-pale) !important; color: var(--gold) !important; text-decoration: line-through; border-radius: 0 !important; }
.flatpickr-day.closed-date { background: var(--danger-bg) !important; color: var(--danger) !important; text-decoration: line-through; border-radius: 0 !important; }
</style>
@else
<style>
.flatpickr-day.booked-date { background: var(--gold-pale) !important; color: var(--gold) !important; text-decoration: line-through; border-radius: 0 !important; }
.flatpickr-day.closed-date { background: var(--danger-bg) !important; color: var(--danger) !important; text-decoration: line-through; border-radius: 0 !important; }
</style>
@endif
<style>
.avail-legend { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 14px; }
.avail-legend-item { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--gray-600); }
.avail-legend-dot { width: 12px; height: 12px; border-radius: 3px; flex-shrink: 0; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>{{ __('Calendar & Availability') }}</h1>
    <a href="{{ route('properties.show', $property) }}" class="btn btn-outline-gold">
        <i class="fas fa-arrow-right ms-1"></i> {{ __('Back to Property') }}
    </a>
</div>

@if(session('success'))
<div class="alert alert-success d-flex align-items-center gap-2" style="background: var(--success-bg); color: var(--success); border: none; border-radius: 10px; padding: 12px 20px; margin-bottom: 20px;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif
@if($errors->any())
<div class="alert alert-danger d-flex align-items-center gap-2" style="background: var(--danger-bg); color: var(--danger); border: none; border-radius: 10px; padding: 12px 20px; margin-bottom: 20px;">
    <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
</div>
@endif

<div class="kpi-card" style="padding: 0; margin-bottom: 24px;">
    <div class="table-toolbar" style="border-bottom-color: var(--blue-soft);">
        <span class="table-title"><i class="fas fa-calendar gold-text ms-1"></i> {{ $property->title }}</span>
        <span class="kpi-delta up" style="font-size: 12px;"><i class="fas fa-info-circle"></i> {{ __('Click dates to block, click blocked dates to unblock') }}</span>
    </div>
    <div style="padding: 24px 28px;">
        <div class="avail-legend">
            <div class="avail-legend-item">
                <div class="avail-legend-dot" style="background:var(--success-bg);border:2px solid var(--success);"></div>
                {{ __('Available') }}
            </div>
            <div class="avail-legend-item">
                <div class="avail-legend-dot" style="background:var(--gold-pale);border:2px solid var(--gold);"></div>
                {{ __('Booked') }}
            </div>
            <div class="avail-legend-item">
                <div class="avail-legend-dot" style="background:var(--danger-bg);border:2px solid var(--danger);"></div>
                {{ __('Blocked by you') }}
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
            <div>
                <input type="text" id="availabilityPicker" class="form-control" style="visibility:hidden;height:0;padding:0;border:none;">
                <div id="calendar-container" style="text-align:center;"></div>
            </div>
            <div>
                <form id="saveForm" method="POST" action="{{ route('owner.properties.availability.store', $property) }}">
                    @csrf
                    <input type="hidden" name="dates" id="selectedDates" value="">
                    <button type="submit" id="saveBtn" class="btn btn-primary w-100" style="display:none;margin-bottom:14px;">
                        <i class="fas fa-save ms-1"></i> {{ __('Save Blocked Dates') }}
                    </button>
                </form>

                <div style="background:var(--white);border-radius:10px;border:1px solid var(--gray-100);overflow:hidden;">
                    <div style="display:flex;align-items:center;gap:8px;padding:10px 14px;border-bottom:1px solid var(--gray-100);font-size:13px;font-weight:700;color:var(--gold);">
                        <i class="fas fa-calendar-check gold-text"></i> {{ __('Upcoming Bookings') }}
                    </div>
                    <div style="padding:4px 14px;">
                        @forelse($upcomingBookings as $booking)
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--gray-100);">
                            <div style="font-size:13px;color:var(--gray-800);">{{ $booking->user->full_name ?? '—' }}</div>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <small style="color:var(--gray-400);font-size:11px;">{{ \Carbon\Carbon::parse($booking->start_date)->translatedFormat('d F') }} — {{ \Carbon\Carbon::parse($booking->end_date)->translatedFormat('d F') }}</small>
                                <span class="badge badge-{{ $booking->status }}" style="font-size:10px;padding:2px 8px;">{{ __($booking->status) }}</span>
                            </div>
                        </div>
                        @empty
                        <p class="text-muted small" style="margin:12px 0;text-align:center;">{{ __('No upcoming bookings') }}</p>
                        @endforelse
                    </div>
                </div>

                <div style="background:var(--white);border-radius:10px;border:1px solid var(--gray-100);overflow:hidden;margin-top:14px;">
                    <div style="display:flex;align-items:center;gap:8px;padding:10px 14px;border-bottom:1px solid var(--gray-100);font-size:13px;font-weight:700;color:var(--gold);">
                        <i class="fas fa-ban gold-text"></i> {{ __('Blocked Dates') }}
                    </div>
                    <div style="padding:4px 14px;">
                        @forelse($closedDates as $date)
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid var(--gray-100);">
                            <span style="color:var(--danger);font-size:13px;"><i class="fas fa-times-circle ms-1" style="font-size:11px;"></i> {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</span>
                            <form method="POST" action="{{ route('owner.properties.availability.remove', $property) }}" onsubmit="return confirm('{{ __('Remove this date?') }}')">
                                @csrf @method('DELETE')
                                <input type="hidden" name="date" value="{{ $date }}">
                                <button class="btn btn-sm text-danger p-0" title="{{ __('Remove') }}"><i class="fas fa-trash-alt" style="font-size:12px;"></i></button>
                            </form>
                        </div>
                        @empty
                        <p class="text-muted small" style="margin:12px 0;text-align:center;">{{ __('No blocked dates') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
@if($ar)
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/ar.js"></script>
@endif
<script>
document.addEventListener('DOMContentLoaded', function() {
    const bookedDates = {!! json_encode($bookedDates) !!};
    const closedDates = {!! json_encode($closedDates) !!};
    const locale = '{{ app()->getLocale() }}';

    const picker = flatpickr('#availabilityPicker', {
        inline: true,
        mode: 'multiple',
        dateFormat: 'Y-m-d',
        defaultDate: closedDates,
        minDate: 'today',
        disable: bookedDates,
        locale: locale === 'ar' ? 'ar' : 'default',
        onChange: function(selectedDates, dateStr) {
            const blocked = selectedDates
                .filter(d => {
                    const ds = flatpickr.formatDate(d, 'Y-m-d');
                    return !bookedDates.includes(ds);
                })
                .map(d => flatpickr.formatDate(d, 'Y-m-d'));
            document.getElementById('selectedDates').value = JSON.stringify(blocked);
            const btn = document.getElementById('saveBtn');
            btn.style.display = blocked.length ? 'block' : 'none';
            if (blocked.length) {
                btn.innerHTML = '<i class="fas fa-save ms-1"></i> '
                    + (locale === 'ar' ? 'حفظ التواريخ المحظورة' : '{{ __("Save Blocked Dates") }}')
                    + ' (' + blocked.length + ')';
            }
        },
        onDayCreate: function(dObj, dStr, fp, dayElem) {
            const dateStr = flatpickr.formatDate(dayElem.dateObj, 'Y-m-d');
            if (bookedDates.includes(dateStr)) {
                dayElem.classList.add('booked-date');
            }
            if (closedDates.includes(dateStr)) {
                dayElem.classList.add('closed-date');
            }
        }
    });

    document.querySelector('.flatpickr-calendar')?.addEventListener('click', function() {
        setTimeout(() => {
            document.querySelectorAll('.flatpickr-day').forEach(el => {
                if (!el.dateObj) return;
                const ds = flatpickr.formatDate(el.dateObj, 'Y-m-d');
                if (bookedDates.includes(ds)) el.classList.add('booked-date');
                if (closedDates.includes(ds)) el.classList.add('closed-date');
            });
        }, 50);
    });
});
</script>
@endpush
