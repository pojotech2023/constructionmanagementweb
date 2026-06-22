<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        @page {
            margin: 0;
        }

        body {
            margin: 0;
            background: #ffffff;
            color: #1f2937;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        .page {
            width: 100%;
            padding-bottom: 24px;
        }

        .header {
            padding: 34px 46px 14px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
            border: 0;
            padding: 0;
        }

        .logo {
            width: 360px;
            max-height: 150px;
            object-fit: contain;
        }

        .company-address {
            color: #073b75;
            font-style: italic;
            font-weight: 700;
            line-height: 1.85;
            text-align: right;
            padding-top: 6px;
        }

        .orange-line {
            height: 5px;
            background: #f58220;
            margin: 0 46px;
        }

        .title-band {
            background: #f2efe8;
            border-top: 1px solid #e5e1d8;
            border-bottom: 1px solid #e5e1d8;
            text-align: center;
            padding: 18px 0 14px;
        }

        .title-band h1 {
            color: #17172d;
            font-size: 22px;
            letter-spacing: 1px;
            margin: 0 0 6px;
        }

        .meta {
            color: #9ca3af;
            font-size: 11px;
        }

        .content {
            padding: 30px 46px 0;
        }

        .site-box {
            border: 1px solid #ddd3b8;
            border-left: 4px solid #d3aa42;
            border-radius: 4px;
            padding: 16px 18px;
            margin-bottom: 24px;
        }

        .site-name {
            color: #101827;
            font-size: 17px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .site-location {
            color: #6b7280;
            font-size: 11px;
        }

        .amount-box {
            background: #17172d;
            border-radius: 5px;
            color: #c7cad1;
            padding: 18px 20px;
            margin-bottom: 24px;
            letter-spacing: 1px;
        }

        .amount-box .amount {
            color: #d7ae42;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 0;
        }

        .section {
            margin-bottom: 24px;
        }

        .section-title {
            background: #17172d;
            color: #d7ae42;
            font-weight: 800;
            padding: 10px 14px;
            letter-spacing: .5px;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
        }

        .details-table td {
            border: 0;
            border-bottom: 1px solid #ececec;
            padding: 11px 14px;
        }

        .details-table tr:nth-child(odd) td {
            background: #fafaf8;
        }

        .label {
            width: 38%;
            font-weight: 800;
            color: #374151;
        }

        .mode-pill {
            display: inline-block;
            min-width: 52px;
            padding: 3px 12px;
            border-radius: 999px;
            background: #d3aa42;
            color: #fff;
            font-size: 10px;
            font-weight: 800;
            text-align: center;
            letter-spacing: .5px;
        }

        .strong {
            font-weight: 800;
            color: #111827;
        }

        .receipt-note {
            margin: 26px 46px 0;
            border-top: 1px dashed #d7d7d7;
            padding-top: 14px;
            color: #6b7280;
            font-size: 10px;
            line-height: 1.6;
        }

        .receipt-note strong {
            color: #6b7280;
        }

        .footer-strip {
            margin-top: 58px;
            background: #17172d;
            color: #c7cad1;
            font-size: 9px;
            padding: 13px 46px;
            text-align: center;
        }

        .footer-strip .brand,
        .footer-strip .link {
            color: #d7ae42;
        }
    </style>
</head>
<body>
    @php
        $site = $payment->site;
        $logoPath = public_path('images/logo/logo.jpeg');
        $receiptNo = str_pad((string) $payment->id, 5, '0', STR_PAD_LEFT);
        $paymentDate = \Carbon\Carbon::parse($payment->date)->format('d M Y');
        $issuedDate = now()->format('d M Y');
        $siteName = optional($site)->site_name ?? 'Site';
        $siteLocation = optional($site)->location;
    @endphp

    <div class="page">
        <div class="header">
            <table class="header-table">
                <tr>
                    <td style="width: 58%;">
                        @if (file_exists($logoPath))
                            <img src="{{ $logoPath }}" class="logo" alt="Pojo Infra360">
                        @else
                            <div style="font-size: 34px; font-weight: 800; color: #073b75;">Pojo Infra360</div>
                        @endif
                    </td>
                    <td class="company-address">
                        No 77, Velachery main road,<br>
                        Near to Tata motors show room,<br>
                        Rajakilpakkam, Tambaram,<br>
                        Chennai 600073
                    </td>
                </tr>
            </table>
        </div>

        <div class="orange-line"></div>

        <div class="title-band">
            <h1>PAYMENT RECEIPT</h1>
            <div class="meta">Receipt #{{ $receiptNo }} &nbsp;&bull;&nbsp; Issued: {{ $issuedDate }}</div>
        </div>

        <div class="content">
            <div class="site-box">
                <div class="site-name">{{ $siteName }}</div>
                @if ($siteLocation)
                    <div class="site-location">{{ $siteLocation }}</div>
                @endif
            </div>

            <div class="amount-box">
                PAYMENT RECEIVED <span class="amount">Rs. {{ number_format((float) $payment->payment, 2) }}</span>
            </div>

            <div class="section">
                <div class="section-title">Payment Details</div>
                <table class="details-table">
                    <tr>
                        <td class="label">Payment Date</td>
                        <td>{{ $paymentDate }}</td>
                    </tr>
                    <tr>
                        <td class="label">Payment Mode</td>
                        <td><span class="mode-pill">{{ $payment->payment_mode }}</span></td>
                    </tr>
                    <tr>
                        <td class="label">Remarks</td>
                        <td>{{ $payment->remarks ?: '-' }}</td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <div class="section-title">Budget Summary</div>
                <table class="details-table">
                    <tr>
                        <td class="label">Budget Amount</td>
                        <td>Rs. {{ number_format((float) $budgetAmount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Total Paid (incl. this)</td>
                        <td>Rs. {{ number_format((float) $totalPaid, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Balance Amount</td>
                        <td class="strong">Rs. {{ number_format((float) $balanceAmount, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="receipt-note">
            This is a computer-generated receipt and does not require a physical signature. For any queries, contact us at
            <strong>No 77, Velachery main road, Near to Tata motors show room, Rajakilpakkam, Tambaram, Chennai 600073.</strong>
        </div>

        <div class="footer-strip">
            <span class="brand">Pojo Infra360</span>
            &nbsp;|&nbsp;
            No 77, Velachery main road, Near to Tata motors show room, Rajakilpakkam, Tambaram, Chennai 600073
        </div>
    </div>
</body>
</html>
