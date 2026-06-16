<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pricing Plans</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            padding: 40px 20px;
        }

        .btn-primary {
            --bs-btn-color: #fff;
            --bs-btn-bg: #1184a7;
            --bs-btn-border-color: #1184a7;
            --bs-btn-hover-color: #fff;
            --bs-btn-hover-bg: #0e6f8a;
            --bs-btn-hover-border-color: #0e6f8a;
            --bs-btn-focus-shadow-rgb: 49, 132, 253;
            --bs-btn-active-color: #fff;
            --bs-btn-active-bg: #1184a7;
            --bs-btn-active-border-color: #1184a7;
            --bs-btn-active-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
            --bs-btn-disabled-color: #fff;
            --bs-btn-disabled-bg: #1184a7;
            --bs-btn-disabled-border-color: #1184a7;
        }

        .pricing-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .plan-card {
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 25px 20px;
            height: 100%;
            transition: 0.3s ease;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }

        .plan-card:hover {
            border: 2px solid #0e6f8a;
            box-shadow: 0 0 0 1px rgba(14, 111, 138, 0.2);
        }

        .toggle-button {
            border: 1px solid #ddd;
            border-radius: 30px;
            display: inline-flex;
            margin: 20px 0;
            overflow: hidden;
        }

        .toggle-button button {
            border: none;
            background: none;
            padding: 10px 20px;
            cursor: pointer;
            white-space: nowrap;
        }

        .toggle-button .active {
            background-color: #1184a7;
            color: white;
        }

        .toggle-button.single-button button {
            background-color: #1184a7;
            color: white;
        }

        .price {
            font-size: 2rem;
            font-weight: bold;
        }

        .price-note {
            font-size: 0.95rem;
            color: #6c757d;
        }

        .site-count {
            font-weight: 600;
            color: #1184a7;
            margin-bottom: 14px;
        }

        .tick-icon {
            background-color: #1184a7;
            color: white;
            display: inline-flex;
            width: 14px;
            height: 14px;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 8px;
            margin-right: 8px;
            flex-shrink: 0;
            position: relative;
            top: 2px;
        }

        .plan-actions {
            margin-top: auto;
            padding-top: 16px;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .feature-text {
            flex: 1;
            line-height: 1.35;
        }

        @media (max-width: 767px) {
            .plan-card {
                margin-bottom: 20px;
            }

            .price {
                font-size: 1.6rem;
            }

            .toggle-button {
                flex-direction: column;
            }

            .toggle-button button {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="mb-4">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-pill">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <div class="pricing-header">
        <h4 class="fw-semibold">Pojo Infra360 Pricing Plans</h4>
        <p>Choose the plan that matches your site count and management needs.</p>

        <div class="d-flex justify-content-center align-items-center mb-3 flex-wrap gap-2">
            <img src="https://randomuser.me/api/portraits/men/1.jpg" class="rounded-circle" width="35" style="margin-left: -15px;" height="35" alt="User">
            <img src="https://randomuser.me/api/portraits/women/2.jpg" class="rounded-circle" width="35" style="margin-left: -15px;" height="35" alt="User">
            <img src="https://randomuser.me/api/portraits/men/3.jpg" class="rounded-circle" width="35" style="margin-left: -15px;" height="35" alt="User">
            <img src="https://randomuser.me/api/portraits/women/4.jpg" class="rounded-circle" width="35" style="margin-left: -15px;" height="35" alt="User">
            <img src="https://randomuser.me/api/portraits/men/5.jpg" class="rounded-circle" width="35" style="margin-left: -15px;" height="35" alt="User">
            <img src="https://randomuser.me/api/portraits/women/6.jpg" class="rounded-circle" style="margin-left: -15px;" width="35" height="35" alt="User">
            <span class="ms-2">Join amazing creators & entrepreneurs</span>
        </div>

        <div class="d-flex justify-content-center flex-wrap align-items-center gap-2">
            <div class="toggle-button single-button">
                <button id="yearlyBtn" class="active">Yearly</button>
            </div>
            <span class="badge bg-success">20% off</span>
        </div>
    </div>

    <div class="row text-center" id="plansContainer"></div>
</div>

<script>
    const plans = [
        {
            title: 'Basic',
            yearly: '4,999',
            sites: '5 Sites',
            desc: 'Essential tools for small projects and daily site management.',
            features: ['Attendance Tracker', 'Materials Management', 'Sub Contractor Management', 'Customer Management', 'Vendor Management', 'Vendor Payment Management', 'Sub Contractor Payment Management', 'Profile', 'Settings'],
            supportLink: 'https://wa.me/918072515050'
        },
        {
            title: 'Starter',
            yearly: '9,999',
            sites: '10 Sites',
            desc: 'Adds quotation workflows for growing teams.',
            features: ['Quotation Generator', 'Attendance Tracker', 'Materials Management', 'Sub Contractor Management', 'Customer Management', 'Vendor Management', 'Vendor Payment Management', 'Sub Contractor Payment Management', 'Profile', 'Settings'],
            supportLink: 'https://wa.me/918072515050'
        },
        {
            title: 'Advanced',
            yearly: '14,999',
            sites: '15 Sites',
            desc: 'Adds attendance exports and property management.',
            features: ['Quotation Generator', 'Attendance Report Download (Excel)', 'Attendance Tracker', 'Materials Management', 'Sub Contractor Management', 'Customer Management', 'Vendor Management', 'Vendor Payment Management', 'Sub Contractor Payment Management', 'Real Estate Property Management', 'Profile', 'Settings'],
            supportLink: 'https://wa.me/918072515050'
        },
        {
            title: 'Advanced Plus',
            yearly: '21,999',
            sites: '20 Sites',
            desc: 'Adds supervisor apps and checklist support.',
            features: ['Quotation Generator', 'Attendance Report Download (Excel)', 'Attendance Tracker', 'Materials Management', 'Sub Contractor Management', 'Customer Management', 'Vendor Management', 'Vendor Payment Management', 'Sub Contractor Payment Management', 'Real Estate Property Management', 'Supervisor Management', 'Supervisor Mobile App', 'Check List (50 nos)', 'Profile', 'Settings'],
            supportLink: 'https://wa.me/918072515050'
        },
        {
            title: 'Professional',
            yearly: '25,000',
            sites: '25 Sites',
            desc: 'Adds admin mobile access for larger operations.',
            features: ['Quotation Generator', 'Attendance Report Download (Excel)', 'Attendance Tracker', 'Materials Management', 'Sub Contractor Management', 'Customer Management', 'Vendor Management', 'Vendor Payment Management', 'Sub Contractor Payment Management', 'Real Estate Property Management', 'Supervisor Management', 'Supervisor Mobile App', 'Admin Mobile App', 'Check List (50 nos)', 'Profile', 'Settings'],
            supportLink: 'https://wa.me/918072515050'
        },
        {
            title: 'Business',
            yearly: '29,999',
            sites: '30 Sites',
            desc: 'Expanded capacity for bigger construction teams.',
            features: ['Quotation Generator', 'Attendance Report Download (Excel)', 'Attendance Tracker', 'Materials Management', 'Sub Contractor Management', 'Customer Management', 'Vendor Management', 'Vendor Payment Management', 'Sub Contractor Payment Management', 'Real Estate Property Management', 'Supervisor Management', 'Supervisor Mobile App', 'Admin Mobile App', 'Check List (50 nos)', 'Profile', 'Settings'],
            supportLink: 'https://wa.me/918072515050'
        },
        {
            title: 'Business Elite',
            yearly: '38,999',
            sites: '35 Sites',
            desc: 'Full suite with Admin, Supervisor and Client app, drawings, and tickets.',
            features: ['Quotation Generator', 'Attendance Report Download (Excel)', 'Attendance Tracker', 'Materials Management', 'Sub Contractor Management', 'Customer Management', 'Vendor Management', 'Vendor Payment Management', 'Sub Contractor Payment Management', 'Real Estate Property Management', 'Supervisor Management', 'Supervisor Mobile App', 'Admin Mobile App', 'Client Mobile App', 'Client Ticket Creation', 'Drawings', 'Check List (50 nos)', 'Profile', 'Settings'],
            supportLink: 'https://wa.me/918072515050'
        }
    ];

    function renderPlans() {
        const container = document.getElementById('plansContainer');
        container.innerHTML = '';

        plans.forEach(plan => {
            const featuresHTML = [`${plan.sites}`].concat(plan.features).map(feature =>
                `<li class="mb-2 feature-item"><span class="tick-icon">&#10004;</span><span class="feature-text">${feature}</span></li>`
            ).join('');

            const footerButton = `<div class="plan-actions"><a href="${plan.supportLink || 'https://wa.me/918072515050'}" target="_blank" class="btn btn-primary w-100">Contact Support</a></div>`;

            container.innerHTML += `
                <div class="col-lg-3 col-md-6 col-sm-12 mb-4 d-flex">
                    <div class="plan-card w-100">
                        <h5>${plan.title}</h5>
                        <p>${plan.desc}</p>
                        <div class="price">Rs. ${plan.yearly}/-</div>
                        <p class="price-note">Yearly plan</p>
                        <ul class="list-unstyled mt-3 text-start">
                            ${featuresHTML}
                        </ul>
                        ${footerButton}
                    </div>
                </div>`;
        });
    }

    renderPlans();
</script>

</body>
</html>
