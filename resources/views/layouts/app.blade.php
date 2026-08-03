<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Pojo Infra360</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="{{ asset('images/logo/logo.jpeg') }}" type="image/x-icon" />

    <!-- Fonts and icons -->
    <script src="{{ asset('admin/assets/js/plugin/webfont/webfont.min.js') }}"></script>
    <script>
        WebFont.load({
            google: {
                families: ["Public Sans:300,400,500,600,700"]
            },
            custom: {
                families: [
                    "Font Awesome 5 Solid",
                    "Font Awesome 5 Regular",
                    "Font Awesome 5 Brands",
                    "simple-line-icons",
                ],
                urls: ["{{ asset('admin/assets/css/fonts.min.css') }}"]
            },
            active: function() {
                sessionStorage.fonts = true;
            },
        });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="{{ asset('admin/assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/assets/css/plugins.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/assets/css/kaiadmin.min.css') }}" />

    <!--icons-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <!--Bootstrap5-->
    {{-- <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet"> --}}

    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link rel="stylesheet" href="{{ asset('admin/assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/assets/css/custom.css') }}" />

    <script>
        // Converts a rupee amount to words using the Indian numbering system (lakh/crore).
        // Available globally on every admin page.
        function numberToWordsIndian(amount) {
            const num = parseFloat(amount);
            if (isNaN(num) || num < 0) return '';

            const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
                'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'
            ];
            const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

            function twoDigits(n) {
                if (n < 20) return ones[n];
                return tens[Math.floor(n / 10)] + (n % 10 ? ' ' + ones[n % 10] : '');
            }

            function threeDigits(n) {
                if (n < 100) return twoDigits(n);
                return ones[Math.floor(n / 100)] + ' Hundred' + (n % 100 ? ' ' + twoDigits(n % 100) : '');
            }

            const rupees = Math.floor(num);
            if (rupees === 0) return 'Zero Rupees Only';

            const crore = Math.floor(rupees / 10000000);
            const lakh = Math.floor((rupees % 10000000) / 100000);
            const thousand = Math.floor((rupees % 100000) / 1000);
            const hundred = rupees % 1000;

            let words = '';
            if (crore) words += threeDigits(crore) + ' Crore ';
            if (lakh) words += threeDigits(lakh) + ' Lakh ';
            if (thousand) words += threeDigits(thousand) + ' Thousand ';
            if (hundred) words += threeDigits(hundred);

            return words.trim() + ' Rupees Only';
        }
    </script>

</head>

<body>
    <div class="wrapper">
        <!--Helpers-->
        @include('admin.partials.helpers')
        <!--End Helpers-->

        <!-- Sidebar -->
        @include('admin.partials.sidebar')
        <!-- End Sidebar -->

        <div class="main-panel">

            {{-- Header --}}
            @include('admin.partials.header')
            {{-- Dashboard --}}
            @yield('content')
            {{-- Footer --}}
            {{-- @include('admin.partials.footer') --}}

        </div>
    </div>
    <!--   Core JS Files   -->
    <script src="{{ asset('admin/assets/js/core/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/core/bootstrap.min.js') }}"></script>

    <!-- jQuery Scrollbar -->
    <script src="{{ asset('admin/assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}"></script>

    <!-- Chart JS -->
    <script src="{{ asset('admin/assets/js/plugin/chart.js/chart.min.js') }}"></script>

    <!-- jQuery Sparkline -->
    <script src="{{ asset('admin/assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js') }}"></script>

    <!-- Chart Circle -->
    <script src="{{ asset('admin/assets/js/plugin/chart-circle/circles.min.js') }}"></script>

    <!-- Datatables -->
    <script src="{{ asset('admin/assets/js/plugin/datatables/datatables.min.js') }}"></script>

    <!-- Bootstrap Notify -->
    {{-- <script src="{{ asset('admin/assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}"></script> --}}

    <!-- jQuery Vector Maps -->
    <script src="{{ asset('admin/assets/js/plugin/jsvectormap/jsvectormap.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/plugin/jsvectormap/world.js') }}"></script>

    <!-- Sweet Alert -->
    <script src="{{ asset('admin/assets/js/plugin/sweetalert/sweetalert.min.js') }}"></script>
    
    <!--Bootstrap5-->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

    <!-- Kaiadmin JS -->
    <script src="{{ asset('admin/assets/js/kaiadmin.min.js') }}"></script>

    <!-- Kaiadmin DEMO methods, don't include it in your project! -->
    <script src="{{ asset('admin/assets/js/setting-demo.js') }}"></script>
    <script src="{{ asset('admin/assets/js/demo.js') }}"></script>

    <script>
        $("#lineChart").sparkline([102, 109, 120, 99, 110, 105, 115], {
            type: "line",
            height: "70",
            width: "100%",
            lineWidth: "2",
            lineColor: "#177dff",
            fillColor: "rgba(23, 125, 255, 0.14)",
        });

        $("#lineChart2").sparkline([99, 125, 122, 105, 110, 124, 115], {
            type: "line",
            height: "70",
            width: "100%",
            lineWidth: "2",
            lineColor: "#f3545d",
            fillColor: "rgba(243, 84, 93, .14)",
        });

        $("#lineChart3").sparkline([105, 103, 123, 100, 95, 105, 115], {
            type: "line",
            height: "70",
            width: "100%",
            lineWidth: "2",
            lineColor: "#ffa534",
            fillColor: "rgba(255, 165, 52, .14)",
        });
    </script>
    {{-- <script>
        document.addEventListener("DOMContentLoaded", function() {
            let alert = document.querySelector('.alert');
            if (alert) {
                setTimeout(() => {
                    alert.classList.remove('show');
                    alert.classList.add('fade');
                    window.location.href = "{{ url()->previous() }}";
                }, 3000); // Hides after 3 seconds
            }
        });
    </script> --}}

    <style>
        /* Amount / GST number fields: whole numbers only, no spinner arrows */
        input[type="number"][name*="amount" i]::-webkit-outer-spin-button,
        input[type="number"][name*="amount" i]::-webkit-inner-spin-button,
        input[type="number"][name*="gst" i]::-webkit-outer-spin-button,
        input[type="number"][name*="gst" i]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type="number"][name*="amount" i],
        input[type="number"][name*="gst" i] {
            -moz-appearance: textfield;
        }
    </style>
    <script>
        // Amount / GST number fields: block decimal entry (whole numbers only), site-wide
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('input[type="number"]').forEach(function (input) {
                var name = (input.getAttribute('name') || '').toLowerCase();
                if (name.indexOf('amount') === -1 && name.indexOf('gst') === -1) {
                    return;
                }

                input.setAttribute('step', '1');

                input.addEventListener('keydown', function (e) {
                    if (e.key === '.' || e.key === ',' || e.key === 'e' || e.key === 'E') {
                        e.preventDefault();
                    }
                });

                input.addEventListener('input', function () {
                    if (input.value.indexOf('.') !== -1) {
                        input.value = input.value.split('.')[0];
                    }
                });
            });
        });
    </script>
</body>

</html>
