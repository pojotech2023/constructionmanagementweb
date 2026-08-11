@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row align-items-center">
        <div class="col-lg-8 offset-lg-1">
            <h3 class="pb-4 mt-3">Sales Bill History — {{ $site->site_name }}</h3>
        </div>
        <div class="col-lg-2 text-end pb-4">
            <a href="{{ route('salesBill.form', $site->id) }}" class="btn btn-primary">+ New Bill</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-11">
            <div class="card shadow-lg p-4 ms-4">

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    {{ session()->forget('success') }}
                @endif

                @if ($bills->isEmpty())
                    <p class="text-center mt-3">No sales bills generated for this site yet.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>S.No</th>
                                    <th>Date</th>
                                    <th>Name</th>
                                    <th>Subject</th>
                                    <th>Total (₹)</th>
                                    <th style="width: 220px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bills as $index => $bill)
                                    @php
                                        $pdfUrl = asset('storage/sales_bills/sales_bill_' . $bill->id . '.pdf');
                                        $waMessage = urlencode("Hi {$bill->name}, your sales bill is ready. Download here: $pdfUrl");
                                        $waLink = "https://wa.me/91{$bill->mobile_no}?text=$waMessage";
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($bill->date)->format('d-m-Y') }}</td>
                                        <td>{{ $bill->name }}</td>
                                        <td>{{ $bill->subject }}</td>
                                        <td>{{ number_format($bill->total_amount, 2) }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ $pdfUrl }}" target="_blank" class="btn btn-sm btn-primary" title="View PDF">
                                                    📄 View
                                                </a>
                                                @if ($bill->mobile_no)
                                                    <a href="{{ $waLink }}" target="_blank" class="btn btn-sm btn-success" title="Share on WhatsApp">
                                                        📤
                                                    </a>
                                                @endif
                                                <button type="button" class="btn btn-sm btn-danger deleteBillBtn"
                                                    data-url="{{ route('salesBill.delete', $bill->id) }}"
                                                    data-name="the sales bill for {{ $bill->name }} ({{ \Carbon\Carbon::parse($bill->date)->format('d-m-Y') }})"
                                                    data-bs-toggle="modal" data-bs-target="#deleteBillModal">
                                                    🗑️
                                                </button>
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

<!-- Delete Bill Modal -->
<div class="modal fade" id="deleteBillModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deleteBillForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="deleteBillName"></strong>? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.addEventListener('click', function (e) {
            const deleteBtn = e.target.closest('.deleteBillBtn');
            if (!deleteBtn) return;

            document.getElementById('deleteBillForm').action = deleteBtn.getAttribute('data-url');
            document.getElementById('deleteBillName').textContent = deleteBtn.getAttribute('data-name') || 'this bill';
        });
    });
</script>
@endsection
