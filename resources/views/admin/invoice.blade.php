<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $shipment->invoice_number ?? $shipment->tracking_number }} - Prime Cargo</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: -apple-system, Helvetica, Arial, sans-serif; color: #1f2933; margin: 0; padding: 40px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #ea580c; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #ea580c; }
        .header .meta { text-align: right; }
        .meta h2 { margin: 0 0 6px; font-size: 18px; }
        .grid { display: flex; gap: 40px; margin-bottom: 30px; }
        .box { flex: 1; }
        .box h3 { font-size: 12px; text-transform: uppercase; color: #6b7280; margin: 0 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { text-align: left; padding: 10px 12px; border-bottom: 1px solid #e5e7eb; }
        th { background: #f9fafb; font-size: 12px; text-transform: uppercase; color: #6b7280; }
        .totals { width: 320px; margin-left: auto; }
        .totals div { display: flex; justify-content: space-between; padding: 6px 0; }
        .totals .grand { font-weight: bold; font-size: 18px; border-top: 2px solid #1f2933; padding-top: 10px; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .badge.paid { background: #dcfce7; color: #166534; }
        .badge.partial { background: #fef3c7; color: #92400e; }
        .badge.unpaid { background: #fee2e2; color: #991b1b; }
        .print-bar { text-align: right; margin-bottom: 20px; }
        @media print { .print-bar { display: none; } }
    </style>
</head>
<body>
    <div class="print-bar">
        <button onclick="window.print()">Print / Save as PDF</button>
    </div>

    <div class="header">
        <div>
            <h1>Prime Cargo</h1>
            <div>UK &harr; Bangladesh Cargo Services</div>
        </div>
        <div class="meta">
            <h2>INVOICE</h2>
            <div>{{ $shipment->invoice_number ?? '—' }}</div>
            <div>{{ $shipment->created_at->format('d M Y') }}</div>
        </div>
    </div>

    <div class="grid">
        @if ($shipment->is_consolidated)
        <div class="box">
            <h3>Consignment</h3>
            <div>Consolidated shipment</div>
            @if ($shipment->parcel_count)<div>{{ $shipment->parcel_count }} parcels</div>@endif
            @if ($shipment->customer)<div>{{ $shipment->customer->name }}</div>@endif
        </div>
        <div class="box">
            <h3>Route</h3>
            <div>{{ strtoupper($shipment->sender_country) }} → {{ strtoupper($shipment->recipient_country) }}</div>
        </div>
        @else
        <div class="box">
            <h3>Sender</h3>
            <div>{{ $shipment->sender_name }}</div>
            <div>{{ $shipment->sender_phone }}</div>
            <div>{{ $shipment->sender_address }}</div>
            <div>{{ strtoupper($shipment->sender_country) }}</div>
        </div>
        <div class="box">
            <h3>Recipient</h3>
            <div>{{ $shipment->recipient_name }}</div>
            <div>{{ $shipment->recipient_phone }}</div>
            <div>{{ $shipment->recipient_address }}</div>
            <div>{{ strtoupper($shipment->recipient_country) }}</div>
        </div>
        @endif
        <div class="box">
            <h3>Shipment</h3>
            <div>Tracking: {{ $shipment->tracking_number }}</div>
            <div>Service: {{ str($shipment->service_type)->replace('_', ' ')->title() }}</div>
            <div>Status: {{ $shipment->status_label }}</div>
            <div>Payment: <span class="badge {{ $shipment->payment_status }}">{{ ucfirst($shipment->payment_status) }}</span></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Weight</th>
                <th>Rate</th>
                <th style="text-align:right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $shipment->description ?: 'Cargo shipment' }}</td>
                <td>{{ $shipment->weight_kg > 0 ? $shipment->weight_kg . ' kg' : '—' }}</td>
                <td>{{ $shipment->weight_kg > 0 ? '£' . number_format($shipment->price_gbp / $shipment->weight_kg, 2) . '/kg' : '—' }}</td>
                <td style="text-align:right">£{{ number_format($shipment->price_gbp ?? 0, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="totals">
        <div><span>Total</span><span>£{{ number_format($shipment->price_gbp ?? 0, 2) }}</span></div>
        <div><span>Paid</span><span>£{{ number_format($shipment->paid_amount, 2) }}</span></div>
        <div class="grand"><span>Balance Due</span><span>£{{ number_format($shipment->balance_due, 2) }}</span></div>
    </div>
</body>
</html>
