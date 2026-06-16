<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Customer Login</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card shadow-lg p-4" style="width: 100%; max-width: 400px; border-radius: 12px;">
        <div class="card-body">
            <h3 class="text-center mb-4">Customer Login</h3>

            <div class="form-group">
                <label for="mobile_no">Mobile Number</label>
                <input type="text" name="mobile_no" id="mobile_no" class="form-control" placeholder="Enter your mobile number">
            </div>

            <button type="button" onclick="login()" class="btn btn-primary btn-block">Login</button>

            <div id="responseArea" class="mt-3 text-danger" style="white-space: pre-wrap;"></div>
        </div>
    </div>
</div>

<!-- jQuery & Bootstrap -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function login() {
        var mobileNo = $('#mobile_no').val();
        var token = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            url: 'http://127.0.0.1:8000/api/login-customer',
            type: 'POST',
            data: JSON.stringify({ mobile_no: mobileNo }),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': token
            },
            success: function(res) {
                $('#responseArea').text(JSON.stringify(res, null, 4));

                if (res.status === true) {
                    // Save token if provided
                    if (res.token) {
                        localStorage.setItem('customer_token', res.token);
                    }

                    // Redirect
                    window.location.href = '/customer-dashboard';
                }
            },
            error: function(xhr) {
                $('#responseArea').text('Error: ' + JSON.stringify(xhr.responseJSON, null, 4));
            }
        });
    }
</script>
</body>
</html>
