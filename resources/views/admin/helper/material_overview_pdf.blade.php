<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ ucfirst($materialType) }} Overview Report</title>
    <style>
        @page {
            margin: 0;
        }

        body {
            margin: 0;
            background: #ffffff;
            color: #1f2937;
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        .page {
            padding: 35px 40px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .header-table td {
            vertical-align: top;
            border: 0;
            padding: 0;
        }

        .logo {
            width: 200px;
            max-height: 85px;
            object-fit: contain;
        }

        .company-address {
            color: #073b75;
            font-style: italic;
            font-weight: 700;
            line-height: 1.6;
            text-align: right;
            font-size: 11px;
        }

        .orange-line {
            height: 4px;
            background: #f58220;
            margin-bottom: 20px;
        }

        .report-title-box {
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-left: 4px solid #073b75;
            padding: 10px 14px;
            margin-bottom: 18px;
        }

        .report-title {
            font-size: 17px;
            font-weight: 900;
            color: #17172d;
            margin: 0 0 4px;
            letter-spacing: .5px;
        }

        .report-meta {
            color: #4b5563;
            font-size: 11px;
        }

        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        table.items-table th {
            background: #17172d;
            color: #d7ae42;
            padding: 8px 6px;
            text-align: left;
            font-weight: 800;
            font-size: 10.5px;
            letter-spacing: .3px;
        }

        table.items-table td {
            border-bottom: 1px solid #e5e7eb;
            padding: 8px 6px;
            font-size: 10.5px;
        }

        table.items-table tr:nth-child(even) td {
            background-color: #f9fafb;
        }

        .badge-type {
            font-weight: 700;
            color: #073b75;
        }

        .footer-strip {
            margin-top: 40px;
            background: #17172d;
            color: #c7cad1;
            font-size: 9px;
            padding: 10px 0;
            text-align: center;
        }

        .footer-strip .brand {
            color: #d7ae42;
            font-weight: 700;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/logo/logo.jpeg');
        $displayMonth = \Carbon\Carbon::parse($month . '-01')->format('F, Y');
        $weekText = $week ? "Week {$week}" : "Full Month";
    @endphp

    <div class="page">
        <table class="header-table">
            <tr>
                <td style="width: 55%;">
                    @if (file_exists($logoPath))
                        <img src="{{ $logoPath }}" class="logo" alt="Pojo Infra360">
                    @else
                        <div style="font-size: 26px; font-weight: 800; color: #073b75;">Pojo Infra360</div>
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

        <div class="orange-line"></div>

        <div class="report-title-box">
            <div class="report-title">{{ strtoupper($materialType) }} OVERVIEW REPORT</div>
            <div class="report-meta">
                <strong>Site:</strong> {{ $site->site_name ?? 'Unknown Site' }}
                &nbsp;|&nbsp;
                <strong>Period:</strong> {{ $displayMonth }} ({{ $weekText }})
                &nbsp;|&nbsp;
                <strong>Generated:</strong> {{ date('d-m-Y h:i A') }}
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 4%;">#</th>
                    <th style="width: 11%;">Date</th>
                    <th style="width: 14%;">Material Type</th>
                    <th style="width: 21%;">Category / Item</th>
                    <th style="width: 11%; text-align: center;">Quantity</th>
                    <th style="width: 17%;">Vendor</th>
                    <th style="width: 11%; text-align: right;">Price (@include('admin.partials.rupee_pdf'))</th>
                    <th style="width: 11%; text-align: right;">Vendor GST</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($materials as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->date ? \Carbon\Carbon::parse($item->date)->format('d-m-Y') : '-' }}</td>
                        <td class="badge-type">{{ $item->material_type_display }}</td>
                        <td>{{ $item->category_display }}</td>
                        <td style="text-align: center;">{{ $item->quantity }} {{ $item->unit_display != '-' ? $item->unit_display : '' }}</td>
                        <td>{{ optional($item->vendor)->name ?? '-' }}</td>
                        <td style="text-align: right;">{{ number_format((float)$item->price, 2) }}</td>
                        <td style="text-align: right;">{{ optional($item->vendor)->gst ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: #9ca3af; padding: 20px;">No materials records found for this period.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="font-weight: 800; background: #e5e7eb;">
                    <td colspan="4" style="text-align: right; font-size: 11.5px; color: #17172d;">TOTAL:</td>
                    <td style="text-align: center; font-size: 11.5px; color: #073b75;">{{ $totalUnits }} Units</td>
                    <td></td>
                    <td style="text-align: right; font-size: 11.5px; color: #073b75;">@include('admin.partials.rupee_pdf') {{ number_format((float)$totalAmount, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <div class="footer-strip">
            <span class="brand">POJO INFRA360</span>
            &nbsp;|&nbsp;
            No 77, Velachery main road, Near to Tata motors show room, Rajakilpakkam, Tambaram, Chennai 600073
        </div>
    </div>
</body>
</html>

