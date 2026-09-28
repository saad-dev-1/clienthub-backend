<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->number }}</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #0A0A0A;
            margin: 0; padding: 0;
            background: #FFFFFF;
            line-height: 1.5;
        }

        /* ============ TOP ACCENT ============ */
        .top-accent {
            height: 6px;
            background: #6D28D9;
            width: 100%;
        }

        .content { padding: 40px 52px; }

        /* ============ HEADER ============ */
        .header-table {
            width: 100%;
            margin-bottom: 30px;
            padding-bottom: 24px;
            border-bottom: 1px solid #E5E5E5;
        }
        .header-table > tbody > tr > td { vertical-align: top; }

        .logo-cell {
            width: 52px;
            vertical-align: middle;
            padding: 0;
        }
        .logo-box {
            width: 52px;
            height: 52px;
            background: #6D28D9;
            border-radius: 13px;
        }
        .logo-inner {
            width: 52px;
            height: 52px;
            border-collapse: collapse;
            margin: 0; padding: 0;
        }
        .logo-inner td {
            width: 52px;
            height: 52px;
            text-align: center;
            vertical-align: middle;
            color: #FFFFFF;
            font-size: 30px;
            font-weight: bold;
            font-family: 'DejaVu Sans', sans-serif;
            line-height: 1;
            padding: 0;
        }

        .brand-text-cell {
            vertical-align: middle;
            padding: 0 0 0 16px;
        }
        .brand-name {
            font-size: 22px;
            font-weight: bold;
            color: #0A0A0A;
            margin: 0;
            line-height: 1;
            letter-spacing: 3.5px;
        }
        .brand-tagline {
            font-size: 9px;
            color: #A3A3A3;
            margin: 6px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 3px;
            font-weight: bold;
        }

        .invoice-meta { text-align: right; }
        .invoice-number-label {
            font-size: 9px;
            color: #6D28D9;
            text-transform: uppercase;
            letter-spacing: 2.5px;
            font-weight: bold;
            margin: 0 0 8px 0;
        }
        .invoice-number {
            font-size: 32px;
            margin: 0 0 20px 0;
            color: #0A0A0A;
            font-weight: bold;
            letter-spacing: -0.5px;
            line-height: 1;
        }

        /* ============ DATES ============ */
        .invoice-dates-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 0;
        }
        .invoice-dates-table td {
            padding: 6px 0;
            font-size: 11px;
            vertical-align: middle;
        }
        .date-label {
            color: #A3A3A3;
            text-align: left;
            width: 60px;
            padding-right: 12px;
            font-weight: bold;
            letter-spacing: 0.5px;
            font-size: 10px;
        }
        .date-value {
            color: #0A0A0A;
            text-align: right;
            font-weight: bold;
            font-size: 12px;
        }

        .status-badge {
            display: inline-block;
            padding: 7px 18px;
            border-radius: 20px;
            font-size: 9px;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 1.5px;
            margin-top: 14px;
        }
        .status-paid { background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; }
        .status-draft { background: #FAFAFA; color: #525252; border: 1px solid #E5E5E5; }
        .status-sent { background: #F5F3FF; color: #6D28D9; border: 1px solid #DDD6FE; }
        .status-overdue { background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA; }

        /* ============ INFO ============ */
        .info-table {
            width: 100%;
            margin-bottom: 30px;
        }
        .info-table td {
            vertical-align: top;
            width: 50%;
        }
        .info-table td.right-cell { text-align: right; }
        .info-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #A3A3A3;
            letter-spacing: 2px;
            font-weight: bold;
            margin: 0 0 10px 0;
        }
        .info-name {
            font-size: 15px;
            color: #0A0A0A;
            font-weight: bold;
            margin: 0 0 6px 0;
            letter-spacing: -0.3px;
        }
        .info-line {
            font-size: 11px;
            color: #525252;
            margin: 3px 0;
            line-height: 1.6;
            word-break: break-word;
        }

        /* ============ ITEMS ============ */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
            table-layout: fixed;
        }
        .items-table thead th {
            background: #F5F3FF;
            padding: 14px 18px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            color: #6D28D9;
            letter-spacing: 1.5px;
            border-top: 1px solid #DDD6FE;
            border-bottom: 1px solid #DDD6FE;
            font-weight: bold;
        }
        .items-table thead th.right { text-align: right; }
        .items-table tbody td {
            padding: 16px 18px;
            border-bottom: 1px solid #F5F5F5;
            font-size: 12px;
            color: #0A0A0A;
            vertical-align: middle;
        }
        .items-table tbody td.right { text-align: right; }
        .items-table tbody td.amount { font-weight: bold; color: #0A0A0A; }
        .items-table tbody tr:last-child td { border-bottom: none; }
        .items-table thead th:last-child,
        .items-table tbody td:last-child { padding-right: 0; }
        .items-table thead { display: table-header-group; }
        .items-table tr { page-break-inside: avoid; }

        /* ============ TOTAL — Clean & Balanced ============ */
        .totals-table {
            width: 100%;
            margin-top: 22px;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .totals-table td {
            vertical-align: middle;
            padding: 0;
            text-align: right;
        }
        .total-bar {
            display: inline-block;
            background: #6D28D9;
            color: #FFFFFF;
            border-radius: 10px;
            padding: 16px 28px;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 0.3px;
            min-width: 200px;
            text-align: left;
        }
        .total-bar-label {
            display: inline-block;
            width: 90px;
            opacity: 0.85;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 2px;
            font-weight: bold;
        }
        .total-bar-value {
            display: inline-block;
            text-align: right;
            font-size: 17px;
            font-weight: bold;
            letter-spacing: -0.3px;
        }

        /* ============ NOTES ============ */
        .notes {
            margin-top: 20px;
            padding: 20px 24px;
            background: #FAFAFA;
            border-left: 3px solid #6D28D9;
            border-radius: 6px;
        }
        .notes h3 {
            font-size: 9px;
            text-transform: uppercase;
            color: #6D28D9;
            margin: 0 0 10px 0;
            letter-spacing: 2px;
            font-weight: bold;
        }
        .notes p {
            margin: 0;
            font-size: 12px;
            line-height: 1.7;
            color: #404040;
        }

        /* ============ FOOTER ============ */
        .footer {
            margin-top: 48px;
            padding-top: 24px;
            text-align: center;
            color: #A3A3A3;
            font-size: 10px;
            border-top: 1px solid #E5E5E5;
        }
        .footer strong { color: #6D28D9; letter-spacing: 1.5px; }
        .footer-tagline {
            margin-top: 8px;
            font-size: 9px;
            color: #D4D4D4;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: bold;
        }
    </style>
</head>
<body>
    @php
        $currencyCode = optional($invoice->user)->currency ?? 'USD';
        $symbols = [
            'USD' => '$', 'PKR' => 'Rs', 'EUR' => '€',
            'GBP' => '£', 'AED' => 'AED', 'INR' => '₹',
        ];
        $currencySymbol = $symbols[$currencyCode] ?? '$';
    @endphp

    <div class="top-accent"></div>

    <div class="content">
        <!-- ============ HEADER ============ -->
        <table class="header-table">
            <tr>
                <td style="width: 50%;">
                    <table style="border-collapse: collapse;">
                        <tr>
                            <td class="logo-cell">
                                <div class="logo-box">
                                    <table class="logo-inner">
                                        <tr><td>K</td></tr>
                                    </table>
                                </div>
                            </td>
                            <td class="brand-text-cell">
                                <p class="brand-name">KLIENT</p>
                                <p class="brand-tagline">Client Portal</p>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%;" class="invoice-meta">
                    <p class="invoice-number-label">Invoice</p>
                    <h1 class="invoice-number">{{ $invoice->number }}</h1>

                    <table class="invoice-dates-table">
                        <tr>
                            <td class="date-label">Issued</td>
                            <td class="date-value">{{ $invoice->issue_date->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <td class="date-label">Due</td>
                            <td class="date-value">{{ $invoice->due_date->format('M d, Y') }}</td>
                        </tr>
                    </table>

                    <span class="status-badge status-{{ $invoice->status }}">
                        {{ $invoice->status }}
                    </span>
                </td>
            </tr>
        </table>

        <!-- ============ INFO ============ -->
        <table class="info-table">
            <tr>
                <td>
                    <p class="info-label">Billed To</p>
                    @if($invoice->client)
                        <p class="info-name">{{ $invoice->client->name }}</p>
                        @if($invoice->client->company)
                            <p class="info-line">{{ $invoice->client->company }}</p>
                        @endif
                        @if($invoice->client->email)
                            <p class="info-line">{{ $invoice->client->email }}</p>
                        @endif
                        @if($invoice->client->phone)
                            <p class="info-line">{{ $invoice->client->phone }}</p>
                        @endif
                    @else
                        <p class="info-line" style="font-style: italic; color: #A3A3A3;">No client specified</p>
                    @endif
                </td>
                <td class="right-cell">
                    <p class="info-label">From</p>
                    <p class="info-name">{{ $invoice->user->name }}</p>
                    <p class="info-line">{{ $invoice->user->email }}</p>
                </td>
            </tr>
        </table>

        <!-- ============ ITEMS ============ -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Description</th>
                    <th class="right" style="width: 15%;">Qty</th>
                    <th class="right" style="width: 15%;">Rate</th>
                    <th class="right" style="width: 20%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="right">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                        <td class="right">{{ $currencySymbol }} {{ number_format($item->rate, 2) }}</td>
                        <td class="right amount">{{ $currencySymbol }} {{ number_format($item->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #A3A3A3; padding: 28px; font-style: italic;">
                            No items added
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- ============ TOTAL ============ -->
        <table class="totals-table">
            <tr>
                <td>
                    <div class="total-bar">
                        <span class="total-bar-label">Total</span>
                        <span class="total-bar-value">{{ $currencySymbol }} {{ number_format($invoice->total, 2) }}</span>
                    </div>
                </td>
            </tr>
        </table>

        <!-- ============ NOTES ============ -->
        @if($invoice->notes)
            <div class="notes">
                <h3>Notes</h3>
                <p>{{ $invoice->notes }}</p>
            </div>
        @endif

        <!-- ============ FOOTER ============ -->
        <div class="footer">
            Thank you for your business. Generated by <strong>KLIENT</strong> on {{ now()->format('M d, Y') }}.
            <div class="footer-tagline">Client Portal for Freelancers</div>
        </div>
    </div>
</body>
</html>