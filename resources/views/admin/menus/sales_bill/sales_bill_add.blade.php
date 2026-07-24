@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-10">
            <h3 class="text-center pb-4 mt-3">Generate Sales Bill</h3>
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

                <form id="salesBillForm" action="{{ route('salesBill.add') }}" method="POST" class="container">
                    @csrf
                    <input type="hidden" name="site_id" value="{{ $site->id }}">

                    <div class="row mt-4">
                        <div class="col-md-2 fw-bold">Name</div>
                        <div class="col-md-4">
                            <input type="text" name="name" class="form-control" placeholder="Customer Name" value="{{ old('name', optional($customer)->name) }}">
                            @error('name') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-2 fw-bold">Mobile No</div>
                        <div class="col-md-4">
                            <input type="text" name="mobile_no" class="form-control" placeholder="Mobile Number" value="{{ old('mobile_no', optional($customer)->mobile_no) }}">
                            @error('mobile_no') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-2 fw-bold">Email</div>
                        <div class="col-md-4">
                            <input type="email" name="email" id="email" class="form-control" placeholder="Email Address" value="{{ old('email', optional($customer)->email) }}">
                            @error('email') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-2 fw-bold">Date</div>
                        <div class="col-md-4">
                            <input type="date" name="date" class="form-control" value="{{ old('date', now()->format('Y-m-d')) }}">
                            @error('date') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-2 fw-bold">Subject</div>
                        <div class="col-md-6">
                            <input type="text" name="subject" class="form-control" placeholder="Subject of Sales Bill" value="{{ old('subject') }}">
                            @error('subject') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-2 fw-bold">Location</div>
                        <div class="col-md-6">
                            <input type="text" name="location" class="form-control" placeholder="Please Enter Location" value="{{ old('location', $site->location) }}">
                            @error('location') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <hr class="mt-5 mb-3">

                    <h5 class="mb-3">Particulars</h5>

                    <div id="particularRows">
                        <div class="row mb-2 particular-row">
                            <div class="col-md-5">
                                <input type="text" name="particular[]" class="form-control" placeholder="Particular (e.g. Bricks)">
                            </div>
                            <div class="col-md-3">
                                <input type="number" step="any" name="count[]" class="form-control count" placeholder="Count">
                            </div>
                            <div class="col-md-3">
                                <input type="number" step="any" name="amount[]" class="form-control amount" placeholder="Amount">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger remove-row">X</button>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-secondary my-3" id="addRow">+ Add Row</button>

                    <div class="row justify-content-end">
                        <div class="col-md-6 text-end">
                            <h5 class="text-primary">Total: ₹ <span id="grandTotal" class="text-dark">0.00</span></h5>
                        </div>
                    </div>

                    <hr class="mt-3 mb-3">

                    <div class="row mt-2">
                        <div class="col-md-2 fw-bold">Terms &amp; Conditions</div>
                        <div class="col-md-8">
                            <textarea name="terms_conditions" class="form-control" rows="4" placeholder="Enter terms &amp; conditions to include in the PDF (optional)">{{ old('terms_conditions') }}</textarea>
                            @error('terms_conditions') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row align-items-center mt-4 g-3 sales-bill-action-row">
                        <div class="col-md-4 text-start">
                            <button type="button" class="btn btn-primary sales-bill-action-btn" id="whatsappButton">Send WhatsApp</button>
                        </div>
                        <div class="col-md-4 text-center">
                            <button type="button" class="btn btn-success sales-bill-action-btn" id="sendMailButton">Send Mail</button>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="button" class="btn btn-outline-primary sales-bill-action-btn" id="downloadPdfButton">Download PDF</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-center mt-3">
    <div class="spinner-border text-primary d-none" role="status" id="loadingSpinner">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let cachedSalesBillLinks = null;
let salesBillDirty = true;

document.addEventListener('DOMContentLoaded', function () {
    const successAlert = document.querySelector(".alert-success");
    if (successAlert) {
        setTimeout(() => {
            successAlert.classList.remove("show");
            successAlert.classList.add("fade");
        }, 300);
    }

    updateGrandTotal();
});

$(document).ready(function () {
    function markSalesBillDirty() {
        salesBillDirty = true;
        cachedSalesBillLinks = null;
    }

    function triggerPdfDownload(pdfUrl) {
        const downloadLink = document.createElement('a');
        downloadLink.href = pdfUrl;
        downloadLink.setAttribute('download', '');
        document.body.appendChild(downloadLink);
        downloadLink.click();
        downloadLink.remove();
    }

    function runSalesBillAction(action, response) {
        if (action === 'whatsapp' && response.whatsapp_url) {
            window.open(response.whatsapp_url, '_blank');
        }

        if (action === 'download' && response.pdf_url) {
            triggerPdfDownload(response.pdf_url);
        }

        if (action === 'mail' && response.mail_sent) {
            alert('Sales bill emailed successfully.');
        }
    }

    function submitSalesBill(action) {
        if (action === 'mail' && !$('#email').val().trim()) {
            alert('Please enter an email address before sending the mail.');
            return;
        }

        if (action !== 'mail' && !salesBillDirty && cachedSalesBillLinks) {
            runSalesBillAction(action, cachedSalesBillLinks);
            return;
        }

        $('#loadingSpinner').removeClass('d-none');

        const form = $('#salesBillForm');
        const formData = form.serialize() + '&action=' + encodeURIComponent(action);

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            success: function (response) {
                $('#loadingSpinner').addClass('d-none');

                if (response.status === 'success') {
                    cachedSalesBillLinks = response;
                    salesBillDirty = false;
                    runSalesBillAction(action, response);
                }
            },
            error: function (xhr) {
                $('#loadingSpinner').addClass('d-none');
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let message = Object.values(errors).map(e => e[0]).join("\n");
                    alert("Validation Errors:\n" + message);
                } else {
                    let errorMsg = xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : "Something went wrong!";
                    alert("Error: " + errorMsg);
                    console.error(xhr);
                }
            }
        });
    }

    $(document).on('input', '.count, .amount', function () {
        updateGrandTotal();
        markSalesBillDirty();
    });

    $(document).on('input change', '#salesBillForm input, #salesBillForm select, #salesBillForm textarea', function () {
        markSalesBillDirty();
    });

    $('#addRow').on('click', function () {
        const row = `
<div class="row mb-2 particular-row">
    <div class="col-md-5">
        <input type="text" name="particular[]" class="form-control" placeholder="Particular (e.g. Bricks)">
    </div>
    <div class="col-md-3">
        <input type="number" step="any" name="count[]" class="form-control count" placeholder="Count">
    </div>
    <div class="col-md-3">
        <input type="number" step="any" name="amount[]" class="form-control amount" placeholder="Amount">
    </div>
    <div class="col-md-1">
        <button type="button" class="btn btn-danger remove-row">X</button>
    </div>
</div>`;
        $('#particularRows').append(row);
        updateGrandTotal();
        markSalesBillDirty();
    });

    $(document).on('click', '.remove-row', function () {
        $(this).closest('.particular-row').remove();
        updateGrandTotal();
        markSalesBillDirty();
    });

    $('#whatsappButton').on('click', function () {
        submitSalesBill('whatsapp');
    });

    $('#downloadPdfButton').on('click', function () {
        submitSalesBill('download');
    });

    $('#sendMailButton').on('click', function () {
        submitSalesBill('mail');
    });

    $('#salesBillForm').on('submit', function (e) {
        e.preventDefault();
        submitSalesBill('whatsapp');
    });
});

function updateGrandTotal() {
    let total = 0;
    $('.amount').each(function () {
        total += parseFloat($(this).val()) || 0;
    });
    $('#grandTotal').text(total.toFixed(2));
}
</script>

<style>
    .sales-bill-action-row {
        margin-bottom: 6px;
    }

    .sales-bill-action-btn {
        min-width: 200px;
    }

    @media (max-width: 767.98px) {
        .sales-bill-action-row .text-start,
        .sales-bill-action-row .text-center,
        .sales-bill-action-row .text-end {
            text-align: center !important;
        }

        .sales-bill-action-btn {
            width: 100%;
        }
    }
</style>

@endsection
