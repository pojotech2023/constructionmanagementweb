<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>POJO INFRA 360</title>
    <style>
        @page {
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
        }

        .page {
            padding: 40px;
            page-break-after: always;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            width: 100px;
            height: auto;
        }

        .firm-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .no-border {
            border: none !important;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .terms-box {
            margin-top: 20px;
        }

        .terms-box .terms-title {
            font-weight: bold;
            margin-bottom: 6px;
        }

        .terms-box .terms-body {
            white-space: pre-line;
            border: 1px solid #000;
            padding: 8px;
        }
    </style>
</head>
<body>

@php
    $details = $data->details ?? collect();
    $rowsPerPage = 20;
    $chunks = $details->chunk($rowsPerPage);
@endphp

@foreach ($chunks as $pageIndex => $chunk)
    <div class="page">

        @if ($pageIndex === 0)
            <div class="header">
                <img src="{{ public_path('images/logo/logo.jpeg') }}" alt="Logo">
                <div class="firm-title">POJO INFRA 360</div>
            </div>

            <table>
                <tr>
                    <td class="no-border text-left">To:</td>
                    <td class="no-border text-right">Date: {{ \Carbon\Carbon::parse($data->date)->format('d.m.Y') }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="no-border text-left" style="font-weight: bold;">{{ $data->name }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="no-border text-left">Subject: {{ $data->subject }}</td>
                </tr>
                <tr>
                    <td class="no-border text-left">Location: {{ $data->location }}</td>
                    <td class="no-border text-right">Site: {{ optional($site)->site_name ?? '-' }}</td>
                </tr>
            </table>
        @endif

        <table>
            <thead>
                <tr>
                    <th style="width: 55%;">Particular</th>
                    <th style="width: 20%;">Count</th>
                    <th style="width: 25%;">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($chunk as $detail)
                    <tr>
                        <td class="text-left">{{ $detail->particular }}</td>
                        <td class="text-right">{{ rtrim(rtrim(number_format((float) $detail->count, 2), '0'), '.') }}</td>
                        <td class="text-right">{{ number_format($detail->amount, 2) }}</td>
                    </tr>
                @endforeach

                @if ($loop->last && $pageIndex === count($chunks) - 1)
                    <tr>
                        <td colspan="2" class="text-center" style="font-weight: bold;">TOTAL</td>
                        <td class="text-right" style="font-weight: bold;">{{ number_format($data->total_amount, 2) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        @if ($pageIndex === count($chunks) - 1 && !empty($data->terms_conditions))
            <div class="terms-box">
                <div class="terms-title">Terms &amp; Conditions</div>
                <div class="terms-body">{{ $data->terms_conditions }}</div>
            </div>
        @endif
    </div>
@endforeach

</body>
</html>
