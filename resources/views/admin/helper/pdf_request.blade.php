<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 0;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            background-image: url('{{ public_path("images/letterhead1.jpg") }}');
            background-repeat: no-repeat;
            background-size: cover;
            background-position: top center;
            font-size: 12px;
        }

        .content {
            padding: 270px 40px 120px 40px; /* adjust top & bottom padding as per letterhead image */
        }

        table {
            width: 100%;
            max-width: 700px;
            border-collapse: collapse;
            margin: 0 auto;
            table-layout: fixed;
        }

        td {
            padding: 6px;
            vertical-align: top;
        }

        td:first-child {
            width: 35%;
            font-weight: bold;
        }

        td:last-child {
            width: 65%;
        }
    </style>
</head>

<body>
    <div class="content">
        <table>
            <tr><td>Vendor Name:</td><td>{{ $vendor_name }}</td></tr>
            <tr><td>Vendor Address:</td><td>{{ $vendor_address }}</td></tr>
            <tr><td>Material Type:</td><td>{{ $request->material_type }}</td></tr>
            <tr><td>Quantity:</td><td>{{ $request->quantity }}</td></tr>
            <tr><td>Unit:</td><td>{{ $request->unit }}</td></tr>
            <tr><td>Delivery Needed By:</td><td>{{ $request->delivery_needed_by }}</td></tr>
            <tr><td>Amount:</td><td>₹{{ $request->amount }}</td></tr>
            <tr><td>Remarks:</td><td>{{ $request->remarks }}</td></tr>
        </table>
    </div>
</body>

</html>
