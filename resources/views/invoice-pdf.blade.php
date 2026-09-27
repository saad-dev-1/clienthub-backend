<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #1f2937;
            margin: 0;
            padding: 30px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #6366F1;
        }
        .brand {
            font-size: 24px;
            font-weight: bold;
            color: #6366F1;
        }
        .invoice-number {
            text-align: right;
        }
        .invoice-number h1 {
            font-size: 28px;
            margin: 0;
            color: #1f2937;
        }
        .invoice-number p {
            margin: 4px 0 0 0;
            color: #6b7280;
            font-size: 11px;
        }
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .info-block h3 {
            font-size: 11px;
            text-transform: uppercase;
            color: #6b7280;
            margin: 0 0 6px 0;
            letter-spacing: 0.5px;
        }
        .info-block p {
            margin: 2px 0;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th {
            background: #f3f4f6;
            padding: 10px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
        }
        table th.right, table td.right {
            text-align: right;
        }
        table td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
        }
        .totals {
            text-align: right;
            margin-top: 20px;
        }
        .totals .total-row {
            display: flex;
            justify-content: flex-end;
            padding: 8px 0;
            font-size: 14px;
        }
        .totals .total-row span:first-child {
            margin-right: 40px;
            color: #6b7280;
        }
        .totals .total-row.grand {
            font-size: 18px;
            font-weight: bold;
            color: #6366F1;
            border-top: 2px solid #6366F1;
            padding-top: 12px;
            margin-top: 8px;
        }
        .notes {
            margin-top: 40px;
            padding: 15px;
            background: #f9fafb;
            border-left: 3px solid #6366F1;
        }
        .notes h3 {
            font-size: 11px;
            text-transform: uppercase;
            color: #6b7280;
            margin: 0 0 8px 0;
        }
        .notes p {
            margin: 0;
            font-size: 12px;
            line-height: 1.5;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: bold;
            margin-top: 8px;
        }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-draft { background: #e5e7eb; color: #374151; }
        .status-sent { background: #dbeafe; color: #1e40af; }
        .status-overdue { background: #fee2e2; color: #991b1b; }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #9ca3af;
            font-size: 10px;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div>
            <div class="brand">ClientHub</div>
            <p style="color: #6b7280; margin: 5px 0 0 0; font-size: 11px;">
                Professional Invoice
            </p>
        </div>
        <div class="invoice-number">
            <h1>{{ $invoice->number }}</h1>
            <p>Issued: {{ $invoice->issue_date->format('M d, Y') }}</p>
            <p>Due: {{ $invoice->due_date->format('M d, Y') }}</p>
            <span class="status-badge status-{{ $invoice->status }}">
                {{ $invoice->status }}
            </span>
        </div>
    </div>

    <!-- Info Section -->
    <div class="info-section">
        <div class="info-block">
            <h3>Billed To</h3>
            @if($invoice->client)
                <p><strong>{{ $invoice->client->name }}</strong></p>
                @if($invoice->client->company)
                    <p>{{ $invoice->client->company }}</p>
                @endif
                @if($invoice->client->email)
                    <p>{{ $invoice->client->email }}</p>
                @endif
                @if($invoice->client->phone)
                    <p>{{ $invoice->client->phone }}</p>
                @endif
            @else
                <p style="color: #9ca3af;">No client specified</p>
            @endif
        </div>
        <div class="info-block" style="text-align: right;">
            <h3>From</h3>
            <p><strong>{{ $invoice->user->name }}</strong></p>
            <p>{{ $invoice->user->email }}</p>
        </div>
    </div>

    <!-- Items Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 50%;">Description</th>
                <th class="right" style="width: 15%;">Qty</th>
                <th class="right" style="width: 15%;">Rate</th>
                <th class="right" style="width: 20%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="right">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                    <td class="right">${{ number_format($item->rate, 2) }}</td>
                    <td class="right">${{ number_format($item->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals">
        <div class="total-row grand">
            <span>Total</span>
            <span>${{ number_format($invoice->total, 2) }}</span>
        </div>
    </div>

    <!-- Notes -->
    @if($invoice->notes)
        <div class="notes">
            <h3>Notes</h3>
            <p>{{ $invoice->notes }}</p>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        Thank you for your business. Generated by ClientHub on {{ now()->format('M d, Y') }}.
    </div>
</body>
</html>