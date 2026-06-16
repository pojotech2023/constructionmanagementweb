<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'DejaVu Sans', sans-serif;
            background-image: url('{{ public_path("images/letterhead1.jpg") }}');
            background-repeat: no-repeat;
            background-size: cover;
            background-position: top center;
            font-size: 12px;
        }

        .content {
            padding: 270px 40px 120px 40px; /* Adjust based on your letterhead image */
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
            <tr><td>Date:</td><td>{{ \Carbon\Carbon::parse($request->date)->format('d-m-y') }}</td></tr>
            <tr><td>Amount:</td><td>₹{{ $request->price }}</td></tr>
        </table>
    </div>
</body>

</html>
