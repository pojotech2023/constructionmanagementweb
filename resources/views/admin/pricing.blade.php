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

        .feature-item.inherited-feature {
            background-color: rgba(17, 132, 167, 0.1);
            border-radius: 6px;
            padding: 6px 8px;
            margin-left: -8px;
            margin-right: -8px;
        }

        .feature-item.inherited-feature .feature-text {
            font-weight: 600;
            color: #1184a7;
        }

        .feature-item.inherited-feature .tick-icon {
            background-color: #1184a7;
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
            features: ['Admin Panel - Web', 'Dashboard', 'Attendance Tracker', 'Materials Management', 'Sub Contractor Management', 'Payment Status', 'Purchase / Sales Bill', 'Customer Management', 'Vendor Management', 'Vendor Payment Management', 'Sub Contractor Payment Management', 'Order Summary', 'Customer Payment Summary', 'Profile', 'Settings'],
            supportLink: 'https://wa.me/918072515050'
        },
        {
            title: 'Starter',
            yearly: '9,999',
            sites: '10 Sites',
            desc: 'Everything in Basic, plus quotation workflows for growing teams.',
            features: ['Everything in Basic Plan', 'Quotation Generator', 'Quotation Share Via Email, WA', 'Payment Receipt Via Email, WA', 'Order Summary Report'],
            supportLink: 'https://wa.me/918072515050'
        },
        {
            title: 'Advance',
            yearly: '14,999',
            sites: '15 Sites',
            desc: 'Everything in Basic, Starter, plus reminders and mobile access.',
            features: ['Everything in Basic, Starter Plan', 'Customer Wishes Reminder', 'Admin Mobile App', 'Full Report Download'],
            supportLink: 'https://wa.me/918072515050'
        },
        {
            title: 'Professional',
            yearly: '21,999',
            sites: '20 Sites',
            desc: 'Everything in Basic, Starter, Advance, plus supervisor tools.',
            features: ['Everything in Basic, Starter, Advance Plan', 'Supervisor Management', 'Supervisor Mobile App'],
            supportLink: 'https://wa.me/918072515050'
        },
        {
            title: 'Business',
            yearly: '29,999',
            sites: '30 Sites',
            desc: 'Everything in Basic, Starter, Advance, Professional, plus drawings and client tools.',
            features: ['Everything in Basic, Starter, Advance, Professional Plan', 'Drawings', 'Check List (50 Nos)', 'Client Ticket Creation', 'Client Mobile App'],
            supportLink: 'https://wa.me/918072515050'
        },
        {
            title: 'Business Elite',
            custom: true,
            sites: 'Custom Sites',
            desc: 'Fully customized plan tailored to your organization.',
            features: ['Customization', 'Contact Support for plan changes'],
            supportLink: 'https://wa.me/918072515050'
        }
    ];

    function renderPlans() {
        const container = document.getElementById('plansContainer');
        container.innerHTML = '';

        plans.forEach(plan => {
            const inheritedFeatures = plan.features.filter(feature => feature.startsWith('Everything in'));
            const ownFeatures = plan.features.filter(feature => !feature.startsWith('Everything in'));
            const orderedFeatures = inheritedFeatures.concat([`${plan.sites}`], ownFeatures);

            const featuresHTML = orderedFeatures.map(feature => {
                const isInherited = feature.startsWith('Everything in');
                const itemClass = isInherited ? 'mb-2 feature-item inherited-feature' : 'mb-2 feature-item';
                return `<li class="${itemClass}"><span class="tick-icon">&#10004;</span><span class="feature-text">${feature}</span></li>`;
            }).join('');

            const footerButton = `<div class="plan-actions"><a href="${plan.supportLink || 'https://wa.me/918072515050'}" target="_blank" class="btn btn-primary w-100">Contact Support</a></div>`;

            const priceHTML = plan.custom
                ? `<div class="price">Custom Pricing</div>`
                : `<div class="price">Rs. ${plan.yearly}/-</div>`;
            const priceNote = plan.custom ? 'Contact us for a quote' : 'Yearly plan';

            container.innerHTML += `
                <div class="col-lg-3 col-md-6 col-sm-12 mb-4 d-flex">
                    <div class="plan-card w-100">
                        <h5>${plan.title}</h5>
                        <p>${plan.desc}</p>
                        ${priceHTML}
                        <p class="price-note">${priceNote}</p>
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
