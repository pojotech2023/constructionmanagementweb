@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-10">
            <h3 class="text-center pb-4 mt-3">Generate Purchase Bill</h3>
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

                <form id="purchaseBillForm" action="{{ route('purchaseBill.add') }}" method="POST" class="container">
                    @csrf
                    <input type="hidden" name="site_id" value="{{ $site->id }}">

                    <div class="row mt-4 position-relative">
                        <div class="col-md-2 fw-bold">Vendor Name</div>
                        <div class="col-md-4">
                            <input type="text" name="name" id="vendor_name" class="form-control" placeholder="Type Vendor Name..." autocomplete="off" value="{{ old('name') }}">
                            <div id="vendor_suggestions" class="list-group position-absolute w-100" style="z-index: 1000; display: none;"></div>
                            @error('name') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-2 fw-bold">Mobile No</div>
                        <div class="col-md-4">
                            <input type="text" name="mobile_no" id="vendor_mobile" class="form-control" placeholder="Mobile Number"
                                maxlength="10" minlength="10" pattern="\d{10}"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);"
                                value="{{ old('mobile_no') }}">
                            @error('mobile_no') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-2 fw-bold">Email</div>
                        <div class="col-md-4">
                            <input type="email" name="email" id="email" class="form-control" placeholder="Email Address" value="{{ old('email') }}">
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
                            <input type="text" name="subject" class="form-control" placeholder="Subject of Purchase Bill" value="{{ old('subject') }}">
                            @error('subject') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-2 fw-bold">Location</div>
                        <div class="col-md-6">
                            <input type="text" name="location" id="vendor_address" class="form-control" placeholder="Please Enter Location" value="{{ old('location', $site->location) }}">
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
                            <small id="grandTotal_words" class="form-text text-muted"></small>
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

                    <div class="row align-items-center mt-4 g-3 purchase-bill-action-row">
                        <div class="col-md-4 text-start">
                            <button type="button" class="btn btn-primary purchase-bill-action-btn" id="whatsappButton">Send WhatsApp</button>
                        </div>
                        <div class="col-md-4 text-center">
                            <button type="button" class="btn btn-success purchase-bill-action-btn" id="sendMailButton">Send Mail</button>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="button" class="btn btn-outline-primary purchase-bill-action-btn" id="downloadPdfButton">Download PDF</button>
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
let cachedPurchaseBillLinks = null;
let purchaseBillDirty = true;

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
    function markPurchaseBillDirty() {
        purchaseBillDirty = true;
        cachedPurchaseBillLinks = null;
    }

    function triggerPdfDownload(pdfUrl) {
        const downloadLink = document.createElement('a');
        downloadLink.href = pdfUrl;
        downloadLink.setAttribute('download', '');
        document.body.appendChild(downloadLink);
        downloadLink.click();
        downloadLink.remove();
    }

    function runPurchaseBillAction(action, response) {
        if (action === 'whatsapp' && response.whatsapp_url) {
            window.open(response.whatsapp_url, '_blank');
        }

        if (action === 'download' && response.pdf_url) {
            triggerPdfDownload(response.pdf_url);
        }

        if (action === 'mail' && response.mail_sent) {
            alert('Purchase bill emailed successfully.');
        }
    }

    function submitPurchaseBill(action) {
        if (action === 'mail' && !$('#email').val().trim()) {
            alert('Please enter an email address before sending the mail.');
            return;
        }

        if (action !== 'mail' && !purchaseBillDirty && cachedPurchaseBillLinks) {
            runPurchaseBillAction(action, cachedPurchaseBillLinks);
            return;
        }

        $('#loadingSpinner').removeClass('d-none');

        const form = $('#purchaseBillForm');
        const formData = form.serialize() + '&action=' + encodeURIComponent(action);

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            success: function (response) {
                $('#loadingSpinner').addClass('d-none');

                if (response.status === 'success') {
                    cachedPurchaseBillLinks = response;
                    purchaseBillDirty = false;
                    runPurchaseBillAction(action, response);
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

    $('#vendor_name').on('input', function () {
        let query = $(this).val();
        if (query.length >= 1) {
            $.ajax({
                url: "{{ route('vendors.search') }}",
                type: 'GET',
                data: { name: query },
                success: function (data) {
                    let suggestions = '';
                    data.forEach(function (vendor) {
                        suggestions += `
                <a href="#"
                    class="list-group-item list-group-item-action vendor-option"
                    data-name="${vendor.name}"
                    data-mobile="${vendor.mobile_no}"
                    data-address="${vendor.address}">
                    ${vendor.name}
                </a>`;
                    });
                    $('#vendor_suggestions').html(suggestions).show();
                }
            });
        } else {
            $('#vendor_suggestions').hide();
        }
    });

    $(document).on('click', '.vendor-option', function (e) {
        e.preventDefault();
        $('#vendor_name').val($(this).data('name'));
        $('#vendor_mobile').val($(this).data('mobile'));
        $('#vendor_suggestions').hide();
        markPurchaseBillDirty();
    });

    $(document).click(function (e) {
        if (!$(e.target).closest('#vendor_name, #vendor_suggestions').length) {
            $('#vendor_suggestions').hide();
        }
    });

    $(document).on('input', '.count, .amount', function () {
        updateGrandTotal();
        markPurchaseBillDirty();
    });

    $(document).on('input change', '#purchaseBillForm input, #purchaseBillForm select, #purchaseBillForm textarea', function () {
        markPurchaseBillDirty();
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
        markPurchaseBillDirty();
    });

    $(document).on('click', '.remove-row', function () {
        $(this).closest('.particular-row').remove();
        updateGrandTotal();
        markPurchaseBillDirty();
    });

    $('#whatsappButton').on('click', function () {
        submitPurchaseBill('whatsapp');
    });

    $('#downloadPdfButton').on('click', function () {
        submitPurchaseBill('download');
    });

    $('#sendMailButton').on('click', function () {
        submitPurchaseBill('mail');
    });

    $('#purchaseBillForm').on('submit', function (e) {
        e.preventDefault();
        submitPurchaseBill('whatsapp');
    });
});

function updateGrandTotal() {
    let total = 0;
    $('.amount').each(function () {
        total += parseFloat($(this).val()) || 0;
    });
    $('#grandTotal').text(total.toFixed(2));
    $('#grandTotal_words').text(numberToWordsIndian(total));
}
</script>

<style>
    .purchase-bill-action-row {
        margin-bottom: 6px;
    }

    .purchase-bill-action-btn {
        min-width: 200px;
    }

    @media (max-width: 767.98px) {
        .purchase-bill-action-row .text-start,
        .purchase-bill-action-row .text-center,
        .purchase-bill-action-row .text-end {
            text-align: center !important;
        }

        .purchase-bill-action-btn {
            width: 100%;
        }
    }
</style>

@endsection
