{{-- مسكن — قالب فاتورة PDF --}}
<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Invoice') }} #{{ $booking->id }}</title>
    <style>
        @page { margin: 25px; }
        body {
            font-family: 'xbriyaz', sans-serif;
            font-size: 13px;
            color: #333;
            line-height: 1.6;
            direction: rtl;
        }
        .invoice-box {
            max-width: 750px;
            margin: auto;
            border: 1px solid #ddd;
            border-radius: 6px;
            overflow: hidden;
        }
        .header {
            background: #1a3a5c;
            color: #fff;
            padding: 25px 35px;
            text-align: center;
        }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 3px 0 0; opacity: .8; font-size: 12px; }
        .meta { padding: 20px 35px; border-bottom: 1px solid #eee; }
        .meta table { width: 100%; border-collapse: collapse; }
        .meta td { padding: 4px 0; vertical-align: top; }
        .meta-label { color: #888; font-size: 10px; }
        .items { padding: 15px 35px; }
        .items table { width: 100%; border-collapse: collapse; }
        .items th {
            background: #f5f5f5;
            padding: 8px 10px;
            text-align: center;
            font-size: 11px;
            border-bottom: 2px solid #1a3a5c;
        }
        .items td { padding: 10px; text-align: center; border-bottom: 1px solid #eee; }
        .items th:first-child, .items td:first-child { text-align: right; }
        .total-row td { font-weight: bold; border-top: 2px solid #1a3a5c !important; padding-top: 12px; color: #1a3a5c; }
        .footer { text-align: center; color: #aaa; font-size: 10px; padding: 15px 35px; border-top: 1px solid #eee; }
        .num { font-family: 'xbriyaz', sans-serif; direction: rtl; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <h1>{{ __('Maskan') }}</h1>
            <p>{{ __('Electronic Invoice') }}</p>
        </div>

        <div class="meta">
            <table>
                <tr>
                    <td style="width:50%">
                        <div class="meta-label">{{ __('Invoice Number') }}</div>
                        <div style="font-size:15px;font-weight:700;">INV-{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</div>
                    </td>
                    <td style="width:50%">
                        <div class="meta-label">{{ __('Issue Date') }}</div>
                        <div style="font-weight:600;">{{ ($booking->completed_at ?? now())->format('Y-m-d') }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="meta-label">{{ __('Tenant') }}</div>
                        <div style="font-weight:600;">{{ $booking->user->full_name }}</div>
                    </td>
                    <td>
                        <div class="meta-label">{{ __('Phone') }}</div>
                        <div style="font-weight:600;" dir="ltr">{{ $booking->user->phone ?? '—' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="meta-label">{{ __('Property') }}</div>
                        <div style="font-weight:600;">{{ $booking->property->title }}</div>
                    </td>
                    <td>
                        <div class="meta-label">{{ __('Owner') }}</div>
                        <div style="font-weight:600;">{{ $booking->property->owner->full_name ?? '—' }}</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="meta-label">{{ __('Stay Period') }}</div>
                        <div style="font-weight:600;">
                            {{ __('From') }} {{ \Carbon\Carbon::parse($booking->start_date)->format('Y-m-d') }}
                            &nbsp;&mdash;&nbsp;
                            {{ __('To') }} {{ \Carbon\Carbon::parse($booking->end_date)->format('Y-m-d') }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="items">
            @php $nights = max(1, \Carbon\Carbon::parse($booking->start_date)->diffInDays(\Carbon\Carbon::parse($booking->end_date))); @endphp
            @php $subtotal = $booking->property->price * $nights; @endphp
            @php $vat = 0; @endphp
            @php $total = $subtotal + $vat; @endphp
            <table>
                <thead>
                    <tr>
                        <th style="text-align:right;">{{ __('Description') }}</th>
                        <th>{{ __('Nights') }}</th>
                        <th>{{ __('Nightly Rate') }}</th>
                        <th>{{ __('Total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align:right;">{{ __('Stay at') }} {{ $booking->property->property_type }} — {{ $booking->property->title }}</td>
                        <td>{{ $nights }}</td>
                        <td class="num">{{ number_format($booking->property->price, 2) }}</td>
                        <td class="num">{{ number_format($subtotal, 2) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="3" style="text-align:left;">{{ __('Grand Total') }}</td>
                        <td class="num">{{ number_format($total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="footer">
            <p>{{ __('Thank you for choosing Maskan') }}</p>
            <p style="margin-top:2px;">{{ __('All rights reserved') }} &copy; {{ date('Y') }} — {{ __('Maskan') }}</p>
        </div>
    </div>
</body>
</html>