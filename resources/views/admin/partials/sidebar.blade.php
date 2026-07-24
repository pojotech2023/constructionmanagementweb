<div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
        <div class="logo-header sidebar-logo-header" data-background-color="dark">
            <a href="{{ route('admin.dashboard') }}" class="logo sidebar-logo-link">
                <img src="{{ asset('images/logo/logo.jpeg') }}" alt="Pojo Infra360" class="navbar-brand sidebar-brand-logo" />
            </a>
            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                    <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                    <i class="gg-menu-left"></i>
                </button>
            </div>
            <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
            </button>
        </div>
    </div>

    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <ul class="nav nav-secondary">

                {{-- Dashboard (Visible to all roles) --}}
                <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}" class="collapsed" aria-expanded="false">
                        <i class="bi bi-speedometer"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                {{-- <li class="nav-item">
                    <a href="" class="collapsed" aria-expanded="false">
                        <i class="fa-solid fa-building"></i>
                        <p>Sites</p>
                    </a>
                </li> --}}

           @if ($sharedMenuVisibility['site_management'] ?? true)
           <li class="nav-item {{ request()->routeIs('sitemanagement.*', 'site.*') ? 'active' : '' }}">
                    <a href="{{ route('sitemanagement.list')  }}" class="collapsed" aria-expanded="false">
                    <i class="far fa-chart-bar"></i>
                    <p>Site Management</p>
                    </a>
                </li>
                @endif

               {{-- <li class="nav-item">
                    <a href="{{ session('role_name') == 'Admin' ? route('sitemanagement.list') : route('admin.dashboard') }}" class="collapsed" aria-expanded="false">
                    <i class="far fa-chart-bar"></i>
                    <p>Site Management</p>
                    </a>
                </li>--}}

                @if ($sharedMenuVisibility['generate_quotation'] ?? true)
                <li class="nav-item {{ request()->routeIs('quotation.*') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#quotationmenu">
                        <i class="fa-solid fa-file"></i>
                        <p>Quotation</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ request()->routeIs('quotation.*') ? 'show' : '' }}" id="quotationmenu">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="{{ route('quotation.form') }}" class="collapsed" aria-expanded="false">
                                    <span class="sub-item">Generate Quotation</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('quotation.history') }}">
                                    <span class="sub-item">Quotation History</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endif

                @if ($sharedMenuVisibility['customer_management'] ?? true)
                <li class="nav-item {{ request()->routeIs('customer.*') ? 'active' : '' }}">
                    <a href="{{ route('customer.list') }}" class="collapsed" aria-expanded="false">
                        <i class="bi bi-people-fill"></i>
                        <p>Customer Management</p>
                    </a>
                </li>
                @endif

                @if ($sharedMenuVisibility['vendor'] ?? true)
                <li class="nav-item {{ request()->routeIs('vendor.*', 'paydetail.*', 'payment.*') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#vendormenu">
                        <i class="bi bi-person-fill-add"></i>
                        <p>Vendor</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ request()->routeIs('vendor.*', 'paydetail.*', 'payment.*') ? 'show' : '' }}" id="vendormenu">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="{{ route('vendor.list') }}" class="collapsed" aria-expanded="false">
                                    <span class="sub-item">Vendor Management</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('vendor.dashboard') }}">
                                    <span class="sub-item">Vendor Dashboard</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endif

                @if ($sharedMenuVisibility['subcontractor'] ?? true)
                <li class="nav-item {{ request()->routeIs('subcontractor.*', 'subpaydetail.*', 'subpayment.*') ? 'active' : '' }}">
                    <a data-bs-toggle="collapse" href="#subcontractormenu">
                        <i class="bi bi-person-fill-add"></i>
                        <p>SubContractor</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse {{ request()->routeIs('subcontractor.*', 'subpaydetail.*', 'subpayment.*') ? 'show' : '' }}" id="subcontractormenu">
                        <ul class="nav nav-collapse">
                            <li>
                                <a href="{{ route('subcontractor.list') }}" class="collapsed" aria-expanded="false">
                                    <span class="sub-item">SubContractor Management</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('subcontractor.dashboard') }}">
                                    <span class="sub-item">SubContractor Dashboard</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endif

                @if ($sharedMenuVisibility['supervisor_creation'] ?? true)
                <li class="nav-item {{ request()->routeIs('supervisor.*') ? 'active' : '' }}">
                    <a href="{{ route('supervisor.list') }}" class="collapsed" aria-expanded="false">
                        <i class="bi bi-person-plus-fill"></i>
                        <p>Supervisor Creation</p>
                    </a>
                </li>
                @endif

                @if ($sharedMenuVisibility['property_list'] ?? true)
                <li class="nav-item {{ request()->routeIs('property-*', 'property.*', 'agent.*') ? 'active' : '' }}">
                    <a href="{{ route('property-list') }}" class="collapsed" aria-expanded="false">
                        <i class="bi bi-person-plus-fill"></i>
                        <p>Property List</p>
                    </a>
                </li>
                @endif

                @if ($sharedMenuVisibility['unit_master'] ?? true)
                <li class="nav-item {{ request()->routeIs('unit.*') ? 'active' : '' }}">
                    <a href="{{ route('unit.list') }}" class="collapsed" aria-expanded="false">
                        <i class="bi bi-rulers"></i>
                        <p>Unit Master</p>
                    </a>
                </li>
                @endif

                @if (session('role_name') == 'Admin')
                <li class="nav-item {{ request()->routeIs('admin.control.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.control.index') }}" class="collapsed" aria-expanded="false">
                        <i class="bi bi-toggles"></i>
                        <p>Admin Control</p>
                    </a>
                </li>
                @endif

                {{-- Logout (Visible to all roles) --}}
                <li class="nav-item">
                    <a href="#"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right"></i>
                        <p>Sign Out</p>
                    </a>
                    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </li>

            </ul>

            <a href="{{ route('admin.pricing') }}" class="sidebar-upgrade-card">
                <div class="sidebar-upgrade-icon">
                    <i class="bi bi-stars"></i>
                </div>
                <div class="sidebar-upgrade-text">
                    <h6>Upgrade plan</h6>
                    <p>{{ $sharedRemainingSiteLimit }} {{ $sharedRemainingSiteLimit == 1 ? 'limit' : 'limits' }} left</p>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
    .sidebar-logo-header {
        padding-left: 80px;
    }

    .sidebar-logo-link {
        display: inline-flex;
        align-items: center;
        margin-left: 8px;
    }

    .sidebar-brand-logo {
        width: auto;
        height: 68px;
        max-width: 150px;
        object-fit: contain;
        display: block;
    }

    .sidebar .sidebar-wrapper .sidebar-content {
        min-height: calc(100vh - 75px);
        display: flex;
        flex-direction: column;
        position: relative;
        padding-bottom: 96px;
    }

    .sidebar .sidebar-wrapper .sidebar-content .nav {
        flex: 1 1 auto;
    }

    .sidebar-upgrade-card {
        position: absolute;
        left: 16px;
        right: 16px;
        bottom: 16px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.18);
        text-decoration: none;
        cursor: pointer;
        transition: transform 0.2s ease, background 0.2s ease;
    }

    .sidebar-upgrade-card:hover {
        transform: translateY(-2px);
        background: rgba(255, 255, 255, 0.12);
    }

    .sidebar-upgrade-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 193, 7, 0.14);
        color: #f7c948;
        font-size: 16px;
        flex-shrink: 0;
    }

    .sidebar-upgrade-text h6,
    .sidebar-upgrade-text p {
        margin: 0;
    }

    .sidebar-upgrade-text h6 {
        color: #ffffff;
        font-size: 15px;
        font-weight: 700;
        line-height: 1.2;
    }

    .sidebar-upgrade-text p {
        margin-top: 4px;
        color: rgba(255, 255, 255, 0.72);
        font-size: 13px;
        line-height: 1.2;
    }
</style>
