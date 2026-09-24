<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Purchase Invoice</title>
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
            padding: 40px 46px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .header-table td {
            vertical-align: top;
            border: 0;
            padding: 0;
        }

        .logo {
            width: 220px;
            max-height: 100px;
            object-fit: contain;
        }

        .company-address {
            color: #073b75;
            font-style: italic;
            font-weight: 700;
            line-height: 1.7;
            text-align: right;
        }

        .orange-line {
            height: 4px;
            background: #f58220;
            margin-bottom: 24px;
        }

        .bill-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .bill-table td {
            vertical-align: top;
            border: 0;
            padding: 0;
            width: 50%;
        }

        .bill-title {
            font-weight: 800;
            color: #d3aa42;
            background: #17172d;
            padding: 6px 10px;
            display: inline-block;
            margin-bottom: 8px;
            letter-spacing: .5px;
        }

        .bill-name {
            font-weight: 800;
            font-size: 13px;
            color: #101827;
        }

        .invoice-title {
            text-align: center;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #17172d;
            margin: 10px 0 4px;
        }

        .invoice-meta {
            width: 100%;
            border-collapse: collapse;
            color: #6b7280;
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 24px;
        }

        .invoice-meta td {
            border: 0;
            padding: 0;
        }

        .invoice-number {
            text-align: left;
            font-size: 16px;
            font-weight: 900;
            color: #17172d;
        }

        .invoice-date {
            text-align: right;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table.items th {
            background: #17172d;
            color: #d7ae42;
            padding: 10px;
            text-align: left;
            font-weight: 800;
            letter-spacing: .3px;
        }

        table.items td {
            border-bottom: 1px solid #ececec;
            padding: 10px;
        }

        .footer-strip {
            margin-top: 58px;
            background: #17172d;
            color: #c7cad1;
            font-size: 9px;
            padding: 13px 0;
            text-align: center;
        }

        .footer-strip .brand {
            color: #d7ae42;
        }
    </style>
</head>
<body>
    @php
        $vendor = $order->vendor;
        $logoPath = public_path('images/logo/logo.jpeg');
        $invoiceNo = !empty($order->order_no) ? $order->order_no : ($order->invoice_no ?: ('PO-' . str_pad((string) $order->id, 5, '0', STR_PAD_LEFT)));
        $orderDate = \Carbon\Carbon::parse($order->date)->format('d M Y');
        $items = isset($orderItems) && $orderItems->count() > 0 ? $orderItems : (isset($orders) && $orders->count() > 0 ? $orders : collect([$order]));
        $totalQty = $items->sum('quantity');
        $subtotal = $items->sum('price');
        $totalGst = (float) $items->sum('gst');
        $grandTotal = $subtotal + $totalGst;
    @endphp

    <div class="page">
        <table class="header-table">
            <tr>
                <td style="width: 58%;">
                    @if (file_exists($logoPath))
                        <img src="{{ $logoPath }}" class="logo" alt="Pojo Infra360">
                    @else
                        <div style="font-size: 28px; font-weight: 800; color: #073b75;">Pojo Infra360</div>
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

        <div class="invoice-title">PURCHASE INVOICE</div>
        <table class="invoice-meta">
            <tr>
                <td class="invoice-number">Invoice #{{ $invoiceNo }}</td>
                <td class="invoice-date">Date: {{ $orderDate }}</td>
            </tr>
        </table>

        <table class="bill-table">
            <tr>
                <td>
                    <div class="bill-title">BILL FROM</div>
                    <div class="bill-name">Pojo Infra360</div>
                    <div>No 77, Velachery main road, Near to Tata motors show room, Rajakilpakkam, Tambaram, Chennai 600073</div>
                </td>
                <td style="text-align: right;">
                    <div class="bill-title">BILL TO</div>
                    <div class="bill-name">{{ optional($vendor)->name ?? '-' }}</div>
                    <div>{{ optional($vendor)->address ?? '-' }}</div>
                    <div>{{ optional($vendor)->mobile_no ?? '-' }}</div>
                    @if (optional($vendor)->gst)
                        <div>GSTIN: {{ $vendor->gst }}</div>
                    @endif
                </td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 22%;">Material Type</th>
                    <th style="width: 33%;">Category / Item</th>
                    <th style="width: 12%; text-align: center;">Qty</th>
                    <th style="width: 14%; text-align: right;">Unit Price</th>
                    <th style="width: 14%; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $idx => $item)
                    @php
                        $itemQty = (float) $item->quantity;
                        $itemPrice = (float) $item->price;
                        $itemUnitPrice = $itemQty > 0 ? $itemPrice / $itemQty : $itemPrice;
                    @endphp
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td><strong>{{ $item->material_type_display }}</strong></td>
                        <td>{{ $item->category_display }}</td>
                        <td style="text-align: center;">{{ $itemQty }} {{ $item->unit_display != '-' ? $item->unit_display : '' }}</td>
                        <td style="text-align: right;">@include('admin.partials.rupee_pdf') {{ number_format($itemUnitPrice, 2) }}</td>
                        <td style="text-align: right;">@include('admin.partials.rupee_pdf') {{ number_format($itemPrice, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="font-weight: bold; background: #f3f4f6;">
                    <td colspan="3" style="text-align: right;">Total:</td>
                    <td style="text-align: center;">{{ $totalQty }} Units</td>
                    <td style="text-align: right;">Subtotal:</td>
                    <td style="text-align: right;">@include('admin.partials.rupee_pdf') {{ number_format($subtotal, 2) }}</td>
                </tr>
                @if ($totalGst > 0)
                <tr style="font-weight: bold; background: #f9fafb;">
                    <td colspan="5" style="text-align: right;">GST:</td>
                    <td style="text-align: right;">@include('admin.partials.rupee_pdf') {{ number_format($totalGst, 2) }}</td>
                </tr>
                @endif
                <tr style="font-weight: 800; font-size: 13px; background: #e5e7eb;">
                    <td colspan="5" style="text-align: right; color: #17172d;">Grand Total:</td>
                    <td style="text-align: right; color: #073b75;">@include('admin.partials.rupee_pdf') {{ number_format($grandTotal, 2) }}</td>
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
