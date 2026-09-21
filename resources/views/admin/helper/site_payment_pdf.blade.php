<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt - {{ $payment->id }}</title>
    <style>
        @page {
            size: a5 landscape;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #111111;
            background: #ffffff;
            font-size: 12px;
        }

        .receipt-container {
            position: relative;
            width: 100%;
            height: 100%;
        }

        /* Top-left subtle curved gray decorative accent */
        .corner-shape {
            position: absolute;
            top: 0;
            left: 0;
            width: 130px;
            height: 95px;
            background-color: #e5e7eb;
            border-bottom-right-radius: 105px 85px;
            z-index: 1;
        }

        .top-section {
            position: relative;
            z-index: 2;
            padding: 28px 40px 0 40px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
            padding: 0;
            border: 0;
        }

        .title-box-cell {
            padding-left: 95px;
            padding-top: 4px;
        }

        .payment-receipt-box {
            display: inline-block;
            border: 1.5px solid #000000;
            padding: 6px 20px;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #000000;
            background: #ffffff;
        }

        .company-cell {
            text-align: right;
            font-size: 11px;
            line-height: 1.4;
            color: #222222;
        }

        .company-title {
            font-size: 15px;
            font-weight: bold;
            color: #000000;
            margin-bottom: 2px;
        }

        .date-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
            margin-bottom: 14px;
        }

        .date-cell {
            text-align: right;
            font-size: 13px;
        }

        .date-dots {
            display: inline-block;
            border-bottom: 1.5px dotted #555555;
            min-width: 170px;
            text-align: center;
            font-weight: bold;
            padding: 0 6px 1px;
            color: #000000;
        }

        .body-section {
            padding: 0 40px;
        }

        .fill-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .fill-table td {
            vertical-align: bottom;
            padding: 0;
            border: 0;
        }

        .fill-label {
            white-space: nowrap;
            font-size: 13.5px;
            color: #111111;
            padding-right: 6px;
        }

        .fill-dots {
            width: 100%;
            border-bottom: 1.5px dotted #555555;
            font-size: 13px;
            font-weight: bold;
            color: #000000;
            padding: 0 6px 1px;
        }

        .blank-dots-line {
            width: 100%;
            border-bottom: 1.5px dotted #555555;
            height: 16px;
            margin-bottom: 15px;
        }

        .payment-modes-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
        }

        .payment-modes-cell {
            font-size: 12.5px;
            font-weight: bold;
            color: #111111;
            letter-spacing: 0.5px;
        }

        .mode-item {
            display: inline-block;
            padding: 1px 4px;
        }

        .mode-active {
            background-color: #000000;
            color: #ffffff;
            padding: 2px 8px;
            border-radius: 2px;
        }

        /* Bottom full-width gray band */
        .footer-band {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 110px;
            background-color: #ebebeb;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 32px;
        }

        .footer-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            border: 0;
        }

        .sig-line {
            border-top: 1px solid #777777;
            width: 200px;
            margin: 0 auto 6px auto;
        }

        .sig-label {
            font-size: 11.5px;
            color: #333333;
        }
    </style>
</head>
<body>
    @php
        $site = $payment->site;
        $customer = \App\Models\Customer::where('site_id', $payment->site_id)
            ->where('is_inactive', 0)
            ->orderBy('id')
            ->first();

        $customerName = $customer ? $customer->name : ($site ? $site->site_name : 'Valued Customer');
        $siteName = optional($site)->site_name ?? 'Site';
        $paymentDate = \Carbon\Carbon::parse($payment->date)->format('d-m-Y');
        $amount = (float) $payment->payment;

        // Branding config
        $appName = config('app.name', 'Pojo Infra360');
        $isPavan = str_contains(strtolower($appName), 'pavan');
        $companyName = $isPavan ? 'PAVAN HOMES' : 'POJO INFRA 360';
        $companyAddress = $isPavan
            ? '46 Kabilar Street, MGR Nagar, Chennai 600078'
            : 'No 77, Velachery main road, Rajakilpakkam, Tambaram, Chennai 600073';
        $companyPhone = $isPavan ? '+91 91717 56690' : '+91 74017 07707';

        // Indian Currency Number to Words converter
        if (!function_exists('siteReceiptNumberToWordsIndian')) {
            function siteReceiptNumberToWordsIndian($num) {
                $num = (float)$num;
                if ($num <= 0) {
                    return 'Zero Rupees Only';
                }

                $ones = [
                    0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
                    6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
                    11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen',
                    15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen'
                ];
                $tens = [
                    0 => '', 2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
                    6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'
                ];

                $convertTwoDigits = function ($n) use ($ones, $tens) {
                    if ($n < 20) {
                        return $ones[$n];
                    }
                    return trim($tens[(int)($n / 10)] . ' ' . $ones[$n % 10]);
                };

                $convertThreeDigits = function ($n) use ($ones, $convertTwoDigits) {
                    $str = '';
                    if ($n >= 100) {
                        $str .= $ones[(int)($n / 100)] . ' Hundred ';
                        $n %= 100;
                    }
                    if ($n > 0) {
                        $str .= $convertTwoDigits($n);
                    }
                    return trim($str);
                };

                $rupees = (int) floor($num);
                $paise = (int) round(($num - $rupees) * 100);

                $crore = (int) floor($rupees / 10000000);
                $rupees %= 10000000;

                $lakh = (int) floor($rupees / 100000);
                $rupees %= 100000;

                $thousand = (int) floor($rupees / 1000);
                $rupees %= 1000;

                $hundreds = $rupees;

                $parts = [];
                if ($crore > 0) {
                    $parts[] = $convertTwoDigits($crore) . ' Crore';
                }
                if ($lakh > 0) {
                    $parts[] = $convertTwoDigits($lakh) . ' Lakh';
                }
                if ($thousand > 0) {
                    $parts[] = $convertTwoDigits($thousand) . ' Thousand';
                }
                if ($hundreds > 0) {
                    $parts[] = $convertThreeDigits($hundreds);
                }

                $words = implode(' ', $parts);
                $result = trim($words) . ' Rupees';

                if ($paise > 0) {
                    $result .= ' and ' . $convertTwoDigits($paise) . ' Paise';
                }

                return $result . ' Only';
            }
        }

        $amountInWords = siteReceiptNumberToWordsIndian($amount);

        // Normalize payment mode
        $rawMode = strtolower(trim((string)$payment->payment_mode));
        $isCash = str_contains($rawMode, 'cash');
        $isCheque = str_contains($rawMode, 'cheque') || str_contains($rawMode, 'check');
        $isBank = str_contains($rawMode, 'bank') || str_contains($rawMode, 'net banking') || str_contains($rawMode, 'neft') || str_contains($rawMode, 'rtgs');
        $isMobile = str_contains($rawMode, 'online') || str_contains($rawMode, 'mobile') || str_contains($rawMode, 'upi') || str_contains($rawMode, 'gpay') || str_contains($rawMode, 'phonepe');

        if (!$isCash && !$isCheque && !$isBank && !$isMobile) {
            $isCash = true;
        }

        $purpose = 'Site Construction - ' . $siteName . ($payment->remarks ? ' (' . $payment->remarks . ')' : '');
    @endphp

    <div class="receipt-container">
        <div class="corner-shape"></div>

        <div class="top-section">
            <table class="header-table">
                <tr>
                    <td class="title-box-cell" style="width: 52%;">
                        <div class="payment-receipt-box">PAYMENT RECEIPT</div>
                    </td>
                    <td class="company-cell" style="width: 48%;">
                        <div class="company-title">{{ $companyName }}</div>
                        <div>{{ $companyAddress }}</div>
                        <div>Phone: {{ $companyPhone }}</div>
                    </td>
                </tr>
            </table>

            <table class="date-table">
                <tr>
                    <td class="date-cell">
                        Date: <span class="date-dots">{{ $paymentDate }}</span>
                    </td>
                </tr>
            </table>
        </div>

        <div class="body-section">
            <table class="fill-table">
                <tr>
                    <td class="fill-label" style="width: 170px;">Received with thanks from</td>
                    <td class="fill-dots">{{ $customerName }}</td>
                </tr>
            </table>

            <table class="fill-table">
                <tr>
                    <td class="fill-label" style="width: 130px;">the sum amount of</td>
                    <td class="fill-dots">Rs. {{ number_format($amount, 2) }} ({{ $amountInWords }})</td>
                </tr>
            </table>

            <div class="blank-dots-line"></div>

            <table class="fill-table">
                <tr>
                    <td class="fill-label" style="width: 125px;">For the purpose of</td>
                    <td class="fill-dots">{{ $purpose }}</td>
                </tr>
            </table>

            <table class="payment-modes-table">
                <tr>
                    <td class="payment-modes-cell">
                        By &nbsp;
                        <span class="mode-item {{ $isCash ? 'mode-active' : '' }}">CASH</span> &nbsp;/&nbsp;
                        <span class="mode-item {{ $isCheque ? 'mode-active' : '' }}">CHEQUE</span> &nbsp;/&nbsp;
                        <span class="mode-item {{ $isBank ? 'mode-active' : '' }}">BANK TRANSFER</span> &nbsp;/&nbsp;
                        <span class="mode-item {{ $isMobile ? 'mode-active' : '' }}">MOBILE PAYMENT</span>
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer-band">
            <table class="footer-table">
                <tr>
                    <td>
                        <div class="sig-line"></div>
                        <div class="sig-label">Received By</div>
                    </td>
                    <td>
                        <div class="sig-line"></div>
                        <div class="sig-label">Authorizing Stamp/Signature</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
