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

        .firm-address {
            font-size: 12px;
            margin-top: 5px;
            line-height: 1.4;
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

        .material-contract {
            font-weight: bold;
            font-style: italic;
        }

        .note-points {
            font-size: 12px;
            line-height: 1.6;
            list-style: decimal;
            padding-left: 20px;
            margin-top: 20px;
        }

        .note-title {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 13px;
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
    $rowsPerPage = 15;
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
                    <td class="no-border text-right">Contractor: {{ $data->contractor }}</td>
                </tr>
            </table>
        @endif

        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width: 35%;">Particular</th>
                    <th colspan="3" class="material-contract" style="width: 65%;">Material Contract</th>
                </tr>
                <tr>
                    <th style="width: 20%;">Rate (₹)</th>
                    <th style="width: 20%;">Units</th>
                    <th style="width: 25%;">Total Cost (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($chunk as $detail)
                    <tr>
                        <td class="text-left">{{ $detail->particular }}</td>
                        <td class="text-right">{{ number_format($detail->rate, 2) }}</td>
                        <td class="text-right">{{ number_format($detail->sqFt, 2) }} {{ $detail->unit }}</td>
                        <td class="text-right">{{ number_format($detail->total_cost, 2) }}</td>
                    </tr>
                @endforeach

                @if ($loop->last && $pageIndex === count($chunks) - 1)
                    <tr>
                        <td colspan="3" class="text-center">TOTAL</td>
                        <td class="text-right">{{ number_format($data->total_amount, 2) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        @if ($pageIndex === count($chunks) - 1)
            <div class="note-title">Note:</div>
            <ol class="note-points">
                <li>The above mentioned rate is for the provided Technical specification & Customer chosen package. Any changes by the client will be charged additionally on a pro-rata basis after mutual discussion.</li>
                <li>The material prices in Make & Basic price list (steel & cement) may vary based on market rates and will be finalized at contract signing.</li>
                <li>GST is applicable as per the latest CGST & SGST revisions and will be applied accordingly when necessary.</li>
                <li>Rate variation up to ±5% for Steel, Cement & RMC will be absorbed by the contractor. Any variation beyond this will be settled by the relevant party. For other materials, price increases beyond basic rates will be payable by the client.</li>
                <li>Material basic rates provided are inclusive of GST and transportation. The vendor/contractor must follow site quality & safety guidelines per JSW One Homes SOP.</li>
            </ol>
        @endif

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
