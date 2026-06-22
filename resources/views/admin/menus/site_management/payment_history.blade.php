@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header mb-3">
                <div>
                    <ul class="breadcrumbs mb-2">
                        <li class="nav-home">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="icon-home"></i>
                            </a>
                        </li>
                        <li class="separator">
                            <i class="icon-arrow-right"></i>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('sitemanagement.list') }}">Site</a>
                        </li>
                        <li class="separator">
                            <i class="icon-arrow-right"></i>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('site.detail', $site->id) }}">{{ $site->site_name }}</a>
                        </li>
                        <li class="separator">
                            <i class="icon-arrow-right"></i>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('site.paymentDetail', $site->id) }}">Payment Detail</a>
                        </li>
                        <li class="separator">
                            <i class="icon-arrow-right"></i>
                        </li>
                        <li class="nav-item">
                            <a href="#">Payment History</a>
                        </li>
                    </ul>
                    <h3 class="fw-bold mb-0">{{ $site->site_name }}</h3>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                        <h4 class="card-title mb-0">Site Payment History</h4>
                        <div class="fw-bold text-primary">
                            Budget Amount: {{ number_format($budgetAmount, 2) }}
                        </div>
                    </div>

                    <div class="text-end mb-3">
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#paymentHistoryExportModal">
                            Export Excel
                        </button>
                    </div>

                    @if ($histories->isEmpty())
                        <p class="text-center mt-3">No payment history found.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0 site-payment-history-table">
                                <thead>
                                    <tr>
                                        <th>S.NO</th>
                                        <th>DATE</th>
                                        <th>PAYMENT</th>
                                        <th>PAYMENT MODE</th>
                                        <th>REMARKS</th>
                                        <th>DOWNLOAD</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($histories as $history)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ \Carbon\Carbon::parse($history->date)->format('d-m-Y') }}</td>
                                            <td>{{ number_format((float) $history->payment, 2) }}</td>
                                            <td>{{ $history->payment_mode }}</td>
                                            <td>{{ $history->remarks ?: '-' }}</td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <a href="{{ route('site.payment.pdf', $history->id) }}" class="btn btn-outline-danger btn-sm">
                                                        &darr; PDF
                                                    </a>
                                                    <a href="{{ route('site.payment.whatsapp', $history->id) }}" target="_blank" class="btn btn-success btn-sm">
                                                        <i class="fab fa-whatsapp"></i> WhatsApp
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Export Payment History Modal -->
    <div class="modal fade" id="paymentHistoryExportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="GET" action="{{ route('site.payment.export', ['id' => $site->id]) }}" class="js-export-modal-form">
                    <div class="modal-header">
                        <h5 class="modal-title">Export Payment History</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">From Date</label>
                            <input type="date" name="from_date" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">To Date</label>
                            <input type="date" name="to_date" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Download</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.js-export-modal-form').forEach(function(form) {
                form.addEventListener('submit', function() {
                    var modalEl = form.closest('.modal');
                    var modal = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;
                    if (modal) {
                        modal.hide();
                    }
                });
            });
        });
    </script>

    <style>
        .site-payment-history-table th,
        .site-payment-history-table td {
            padding: 16px 24px;
            border-color: #e5e7eb !important;
        }

        .site-payment-history-table thead th {
            background: #f8f9fa;
            color: #000;
            font-weight: 700;
            letter-spacing: .04em;
        }

        .page-header .breadcrumbs {
            align-items: center;
        }
    </style>
@endsection
