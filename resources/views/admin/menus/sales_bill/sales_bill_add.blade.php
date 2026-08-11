@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row align-items-center">
        <div class="col-lg-8 offset-lg-1">
            <h3 class="pb-4 mt-3">Generate Sales Bill</h3>
        </div>
        <div class="col-lg-2 text-end pb-4">
            <a href="{{ route('salesBill.history', $site->id) }}" class="btn btn-outline-primary">🕘 Bill History</a>
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
                            <input type="text" name="mobile_no" class="form-control" placeholder="Mobile Number"
                                maxlength="10" minlength="10" pattern="\d{10}"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);"
                                value="{{ old('mobile_no', optional($customer)->mobile_no) }}">
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

                    <h5 class="mb-3">Particulars <span class="text-danger">*</span></h5>

                    <div class="row mb-1 d-none d-md-flex">
                        <div class="col-md-3 fw-bold small text-muted">Particular</div>
                        <div class="col-md-2 fw-bold small text-muted">Count</div>
                        <div class="col-md-2 fw-bold small text-muted">Unit</div>
                        <div class="col-md-2 fw-bold small text-muted">Rate (₹)</div>
                        <div class="col-md-2 fw-bold small text-muted">Total (₹)</div>
                    </div>

                    <div id="particularRows">
                        <div class="row mb-2 particular-row">
                            <div class="col-md-3">
                                <input type="text" name="particular[]" class="form-control" placeholder="Particular (e.g. Bricks)" required>
                            </div>
                            <div class="col-md-2">
                                <input type="number" step="any" name="count[]" class="form-control count no-arrow" placeholder="Count">
                            </div>
                            <div class="col-md-2">
                                <select name="unit[]" class="form-control unit-select">
                                    <option value="" style="color:#6c757d;">Unit</option>
                                    @foreach ($units as $u)
                                        <option value="{{ $u->name }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" step="any" name="amount[]" class="form-control amount no-arrow" placeholder="Rate">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control row-total no-arrow" placeholder="0.00" readonly tabindex="-1">
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

                    <hr class="mt-4 mb-3">

                    <div class="row align-items-center">
                        <div class="col-12 text-center">
                            <button type="button" class="btn btn-outline-danger sales-bill-action-btn" id="resetFormButton" data-bs-toggle="modal" data-bs-target="#resetFormModal">Reset</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<!-- Reset Confirm Modal -->
<div class="modal fade" id="resetFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Reset</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Reset the form? All entered data will be lost.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmResetFormButton">Reset</button>
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
const unitOptions = @json($units->pluck('name'));
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
        let missingParticular = false;
        $('#particularRows .particular-row input[name="particular[]"]').each(function () {
            if (!$(this).val().trim()) {
                missingParticular = true;
            }
        });
        if (missingParticular) {
            alert('Particular is mandatory for every row. Please fill it in before continuing.');
            return;
        }

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
        updateRowTotal($(this).closest('.particular-row'));
        updateGrandTotal();
        markSalesBillDirty();
    });

    $(document).on('input change', '#salesBillForm input, #salesBillForm select, #salesBillForm textarea', function () {
        markSalesBillDirty();
    });

    $(document).on('change', '.unit-select', function () {
        $(this).toggleClass('has-value', $(this).val() !== '');
    });

    $('#addRow').on('click', function () {
        const unitSelectOptions = '<option value="" style="color:#6c757d;">Unit</option>' +
            unitOptions.map(u => `<option value="${u}">${u}</option>`).join('');
        const row = `
<div class="row mb-2 particular-row">
    <div class="col-md-3">
        <input type="text" name="particular[]" class="form-control" placeholder="Particular (e.g. Bricks)" required>
    </div>
    <div class="col-md-2">
        <input type="number" step="any" name="count[]" class="form-control count no-arrow" placeholder="Count">
    </div>
    <div class="col-md-2">
        <select name="unit[]" class="form-control unit-select">${unitSelectOptions}</select>
    </div>
    <div class="col-md-2">
        <input type="number" step="any" name="amount[]" class="form-control amount no-arrow" placeholder="Rate">
    </div>
    <div class="col-md-2">
        <input type="text" class="form-control row-total no-arrow" placeholder="0.00" readonly tabindex="-1">
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

    $('#confirmResetFormButton').on('click', function () {
        salesBillDirty = false;
        window.location.reload();
    });

    $('#salesBillForm').on('submit', function (e) {
        e.preventDefault();
        submitSalesBill('whatsapp');
    });
});

function updateRowTotal($row) {
    const count = parseFloat($row.find('.count').val());
    const amount = parseFloat($row.find('.amount').val()) || 0;
    // Count is optional for lump-sum particulars — treat a blank/zero count as
    // "not a quantity multiplier" so Rate alone still totals correctly.
    const rowTotal = (count > 0 ? count : 1) * amount;
    $row.find('.row-total').val(rowTotal ? rowTotal.toFixed(2) : '');
    return rowTotal;
}

function updateGrandTotal() {
    let total = 0;
    $('.particular-row').each(function () {
        total += updateRowTotal($(this));
    });
    $('#grandTotal').text(total.toFixed(2));
    $('#grandTotal_words').text(numberToWordsIndian(total));
}
</script>

<style>
    .unit-select {
        color: #6c757d;
    }

    .unit-select.has-value {
        color: #212529;
    }

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
