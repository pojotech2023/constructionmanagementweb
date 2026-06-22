<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pojo Infra360 Login</title>
    <link rel="icon" href="{{ asset('images/logo/logo.jpeg') }}" type="image/x-icon" />
    <link href="{{ asset('admin/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/assets/css/fonts.min.css') }}" rel="stylesheet">
    <style>
        :root {
            --blue: #1f73e8;
            --blue-dark: #0e4ea8;
            --green: #29c84a;
            --ink: #101a33;
            --muted: #64748b;
            --line: #dbe4f0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: "Inter", "Segoe UI", Arial, sans-serif;
            color: var(--ink);
            background:
                linear-gradient(90deg, rgba(248, 251, 255, .94) 0%, rgba(248, 251, 255, .82) 42%, rgba(15, 23, 42, .22) 100%),
                url("{{ asset('images/sri/home1.jpg') }}") center/cover no-repeat fixed;
        }

        .login-page {
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(0, 1fr) 500px;
            gap: 48px;
            align-items: center;
            padding: 48px 64px;
            position: relative;
            overflow: hidden;
        }

        .login-page::before,
        .login-page::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }

        .login-page::before {
            width: 360px;
            height: 360px;
            left: -110px;
            bottom: -130px;
            background: rgba(41, 200, 74, .13);
        }

        .login-page::after {
            width: 420px;
            height: 420px;
            right: 280px;
            top: -190px;
            border: 70px solid rgba(31, 115, 232, .09);
        }

        .brand-side,
        .login-card {
            position: relative;
            z-index: 1;
        }

        .brand-side {
            max-width: 720px;
        }

        .brand-logo {
            width: 138px;
            height: 96px;
            object-fit: contain;
            background: rgba(255, 255, 255, .86);
            border: 1px solid rgba(255, 255, 255, .72);
            border-radius: 16px;
            padding: 12px;
            box-shadow: 0 20px 55px rgba(15, 23, 42, .16);
            backdrop-filter: blur(14px);
            margin-bottom: 34px;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 9px 14px;
            border-radius: 999px;
            color: #0d5f24;
            background: rgba(41, 200, 74, .14);
            border: 1px solid rgba(41, 200, 74, .24);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .12em;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .eyebrow::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--green);
            box-shadow: 0 0 0 5px rgba(41, 200, 74, .16);
        }

        .brand-side h1 {
            max-width: 620px;
            margin: 0 0 18px;
            color: var(--ink);
            font-size: 54px;
            line-height: 1.06;
            font-weight: 900;
            letter-spacing: 0;
        }

        .brand-side h1 span {
            color: var(--blue);
        }

        .brand-side p {
            max-width: 650px;
            margin: 0;
            color: #42526a;
            font-size: 17px;
            line-height: 1.7;
            font-weight: 600;
        }

        .hero-support {
            display: block;
            margin-top: 8px;
            color: #42526a;
            font-size: 20px;
            line-height: 1.45;
            font-weight: 800;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            max-width: 720px;
            margin-top: 34px;
        }

        .feature-card {
            min-height: 150px;
            border-radius: 18px;
            overflow: hidden;
            position: relative;
            border: 1px solid rgba(255, 255, 255, .76);
            box-shadow: 0 20px 42px rgba(15, 23, 42, .16);
            background: #fff;
        }

        .feature-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .feature-card::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, transparent 34%, rgba(15, 23, 42, .72));
        }

        .feature-card span {
            position: absolute;
            left: 14px;
            bottom: 14px;
            z-index: 1;
            color: #fff;
            font-weight: 900;
            font-size: 14px;
        }

        .login-card {
            width: 100%;
            padding: 42px;
            border-radius: 24px;
            background: rgba(255, 255, 255, .88);
            border: 1px solid rgba(255, 255, 255, .72);
            box-shadow: 0 30px 90px rgba(15, 23, 42, .2);
            backdrop-filter: blur(22px);
        }

        .login-card-header {
            margin-bottom: 30px;
        }

        .access-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 13px;
            border-radius: 999px;
            margin-bottom: 18px;
            color: var(--blue-dark);
            background: rgba(31, 115, 232, .1);
            border: 1px solid rgba(31, 115, 232, .22);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .login-card h2 {
            margin: 0 0 10px;
            font-size: 34px;
            line-height: 1.1;
            font-weight: 900;
            letter-spacing: 0;
        }

        .login-card h2 span {
            color: var(--blue);
        }

        .login-card .subtitle {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
        }

        .form-label {
            font-weight: 900;
            font-size: 13px;
            color: var(--ink);
            margin-bottom: 9px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9aa7b8;
            font-size: 16px;
            pointer-events: none;
        }

        .form-control {
            height: 54px;
            border-radius: 13px;
            border: 1px solid var(--line);
            background: rgba(248, 250, 252, .92);
            padding: 0 16px 0 46px;
            color: var(--ink);
            font-size: 15px;
        }

        .form-control:focus {
            background: #fff;
            border-color: var(--blue);
            box-shadow: 0 0 0 4px rgba(31, 115, 232, .12);
        }

        .login-submit {
            width: 100%;
            height: 56px;
            border: 0;
            border-radius: 14px;
            margin-top: 4px;
            color: #fff;
            background: linear-gradient(135deg, var(--blue), var(--blue-dark));
            font-weight: 900;
            box-shadow: 0 20px 32px rgba(31, 115, 232, .28);
        }

        .login-submit:hover {
            color: #fff;
            background: linear-gradient(135deg, #2c83ff, #0b479b);
        }

        .secure-note {
            margin-top: 24px;
            padding: 16px;
            border-radius: 16px;
            background: rgba(31, 115, 232, .06);
            color: #526178;
            font-size: 13px;
            line-height: 1.55;
            text-align: center;
        }

        .alert {
            border-radius: 14px;
            font-size: 14px;
        }

        @media (max-width: 1100px) {
            .login-page {
                grid-template-columns: 1fr;
                padding: 34px 22px;
            }

            .brand-side {
                max-width: none;
            }

            .brand-side h1 {
                font-size: 42px;
            }

            .login-card {
                max-width: 520px;
                margin: 0 auto;
            }
        }

        @media (max-width: 640px) {
            .feature-grid {
                grid-template-columns: 1fr;
            }

            .brand-side h1,
            .login-card h2 {
                font-size: 32px;
            }

            .login-card {
                padding: 28px 22px;
            }
        }
    </style>
</head>
<body>
    <main class="login-page">
        <section class="brand-side">
            <img src="{{ asset('images/logo/logo.jpeg') }}" alt="Pojo Infra360" class="brand-logo">
            <div class="eyebrow">Construction control center</div>
            <h1>All-in-one construction management software.</h1>
            <p><strong>Pojo Infra360</strong> is designed for builders, contractors, architects, and construction companies. <span class="hero-support">Manage attendance, materials, vendors, subcontractors, payments, site budgets, checklist, tickets, and drawing in one place.</span></p>

            <div class="feature-grid">
                <div class="feature-card">
                    <img src="https://images.unsplash.com/photo-1503387762-592deb58ef4e?auto=format&fit=crop&w=900&q=80" alt="Admin construction planning">
                    <span>Admin</span>
                </div>
                <div class="feature-card">
                    <img src="https://images.unsplash.com/photo-1541888946425-d81bb19240f5?auto=format&fit=crop&w=900&q=80" alt="Supervisor at construction site">
                    <span>Supervisor</span>
                </div>
                <div class="feature-card">
                    <img src="https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=900&q=80" alt="Customer meeting">
                    <span>Customer</span>
                </div>
            </div>
        </section>

        <section class="login-card">
            <div class="login-card-header">
                <div class="access-badge"><i class="fas fa-shield-alt"></i> Admin Access</div>
                <h2>Sign <span>In</span></h2>
                <p class="subtitle">Enter your credentials to access Pojo Infra360.</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <p class="mb-0">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login') }}">
                @csrf
                <div class="form-group mb-4">
                    <label for="mobile_no" class="form-label">Mobile Number</label>
                    <div class="input-wrap">
                        <i class="fas fa-phone"></i>
                        <input type="text" name="mobile_no" id="mobile_no" class="form-control"
                            placeholder="Enter your mobile number" maxlength="10" inputmode="numeric"
                            value="{{ old('mobile_no') }}">
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="password" class="form-control"
                            placeholder="Enter your password">
                    </div>
                </div>

                <button type="submit" class="btn login-submit">
                    <i class="fas fa-sign-in-alt mr-2"></i> Sign In to Dashboard
                </button>
            </form>

            <div class="secure-note">
                Secure access for site finance, attendance, vendor, and subcontractor workflows.
            </div>
        </section>
    </main>
</body>
</html>
