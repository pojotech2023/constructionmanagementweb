@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
                <div>
                    <h3 class="fw-bold mb-3">Dashboard</h3>
                </div>
            </div>

            <div class="container px-0">
                <div class="page-inner px-0">
                    <h3 class="fw-bold mb-1">Sites Overview</h3>
                    <div class="row">
                        <div class="col-sm-6 col-md-3">
                            <a href="{{ route('admin.pricing') }}" style="text-decoration: none;">
                                <div class="card card-stats card-warning card-round">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-5">
                                                <div class="icon-big text-center">
                                                    <i class="bi bi-stars"></i>
                                                </div>
                                            </div>
                                            <div class="col-7 col-stats">
                                                <div class="numbers">
                                                    <p class="card-category">Current Plan</p>
                                                    <h4 class="card-title">{{ $sharedCurrentPlanName }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <a href="{{ url('admin/site-management') }}" style="text-decoration: none;">
                                <div class="card card-stats card-primary card-round">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-5">
                                                <div class="icon-big text-center">
                                                    <i class="bi bi-building"></i>
                                                </div>
                                            </div>
                                            <div class="col-7 col-stats">
                                                <div class="numbers">
                                                    <p class="card-category">Total Site Limit</p>
                                                    <h4 class="card-title">{{ $totalSites }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <a href="{{ url('admin/site-management') }}" style="text-decoration: none;">
                                <div class="card card-stats card-info card-round">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-5">
                                                <div class="icon-big text-center">
                                                    <i class="bi bi-building-add"></i>
                                                </div>
                                            </div>
                                            <div class="col-7 col-stats">
                                                <div class="numbers">
                                                    <p class="card-category">Active Sites</p>
                                                    <h4 class="card-title">{{ $addedSites }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                    </div>

                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mt-4 mb-3">
                        <h4 class="fw-bold mb-0">Site Budget Graph</h4>
                        <div>
                            <label for="statusFilter" class="form-label fw-bold me-2 mb-0">Filter by Sites:</label>
                            <select id="statusFilter" class="form-select w-auto d-inline-block">
                                <option value="All">All</option>
                                <option value="Ongoing" selected>Ongoing</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>
                    </div>

                    <div class="row" id="chartsContainer">
                        @forelse ($charts as $index => $chart)
                            <div class="col-md-4 chart-card" data-status="{{ $chart['status'] }}">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="card-title">{{ $chart['site_name'] }}</div>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container dashboard-chart-container">
                                            <canvas id="doughnutChart{{ $index }}" width="200" height="200"></canvas>
                                        </div>
                                        <div class="dashboard-chart-summary">
                                            <div>
                                                <span><i class="chart-swatch chart-swatch-budget"></i>Budget Amount</span>
                                                <strong>₹{{ number_format((float) $chart['budget_amount'], 2) }}</strong>
                                            </div>
                                            <div>
                                                <span><i class="chart-swatch chart-swatch-expenses"></i>Expenses</span>
                                                <strong>₹{{ number_format((float) $chart['expense_amount'], 2) }}</strong>
                                            </div>
                                            <div>
                                                <span><i class="chart-swatch chart-swatch-balance"></i>Balance Amount</span>
                                                <strong>₹{{ number_format((float) $chart['balance_amount'], 2) }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-5" id="noSitesMessage">
                                <i class="bi bi-building-slash" style="font-size: 4rem; color: #ccc;"></i>
                                <h5 class="mt-3 text-muted">No Sites Found</h5>
                                <p class="text-muted">There are no sites available for the selected filter.</p>
                                <a href="{{ route('site.form') }}" class="btn btn-primary mt-2">
                                    <i class="bi bi-plus-circle me-1"></i> Add New Site
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('admin/assets/js/plugin/chart.js/chart.min.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const backgroundColors = ["#f3545dcc", "#58ef5d", "#1d7af3b8"];
            const chartLabels = ["Expenses", "Balance", "Budget"];
            const chartData = @json($charts);

            chartData.forEach(function(item, index) {
                const canvas = document.getElementById(`doughnutChart${index}`);
                if (!canvas) return;

                new Chart(canvas.getContext("2d"), {
                    type: "doughnut",
                    data: {
                        datasets: [{
                            data: item.data,
                            backgroundColor: backgroundColors,
                        }],
                        labels: chartLabels,
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: {
                            display: false,
                        },
                        plugins: {
                            legend: {
                                display: false,
                            }
                        },
                        layout: {
                            padding: 20
                        }
                    }
                });
            });

            const statusFilter = document.getElementById("statusFilter");
            if (!statusFilter) return;

            statusFilter.addEventListener("change", function() {
                const selectedStatus = this.value;
                const allCharts = document.querySelectorAll(".chart-card");
                let visibleCount = 0;

                allCharts.forEach(function(card) {
                    const cardStatus = card.getAttribute("data-status");
                    const shouldShow = selectedStatus === "All" || cardStatus === selectedStatus;
                    card.style.display = shouldShow ? "block" : "none";
                    if (shouldShow) visibleCount++;
                });

                let noMsg = document.getElementById("noSitesMessage");
                if (allCharts.length > 0) {
                    if (visibleCount === 0) {
                        if (!noMsg) {
                            noMsg = document.createElement("div");
                            noMsg.id = "noSitesMessage";
                            noMsg.className = "col-12 text-center py-5";
                            noMsg.innerHTML = `<i class="bi bi-building-slash" style="font-size:4rem;color:#ccc;"></i>
                                <h5 class="mt-3 text-muted">No Sites Found</h5>
                                <p class="text-muted">No sites match the selected filter.</p>`;
                            document.getElementById("chartsContainer").appendChild(noMsg);
                        } else {
                            noMsg.style.display = "block";
                        }
                    } else if (noMsg) {
                        noMsg.style.display = "none";
                    }
                }
            });

            statusFilter.dispatchEvent(new Event("change"));
        });
    </script>
    <style>
        .dashboard-chart-container {
            min-height: 260px;
            position: relative;
        }

        .dashboard-chart-summary {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 12px 18px;
            padding: 0 18px 10px;
            font-size: 14px;
        }

        .dashboard-chart-summary span {
            color: #6c757d;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .dashboard-chart-summary strong {
            display: none;
        }

        .chart-swatch {
            width: 32px;
            height: 10px;
            display: inline-block;
            border-radius: 1px;
        }

        .chart-swatch-expenses {
            background: #f3545dcc;
        }

        .chart-swatch-balance {
            background: #58ef5d;
        }

        .chart-swatch-budget {
            background: #1d7af3b8;
        }
    </style>
@endsection
