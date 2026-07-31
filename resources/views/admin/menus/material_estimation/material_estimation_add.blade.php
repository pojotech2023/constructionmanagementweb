@extends('layouts.app')

@section('content')
<style>
    input[name="quantity[]"]::-webkit-outer-spin-button,
    input[name="quantity[]"]::-webkit-inner-spin-button,
    #edit_quantity::-webkit-outer-spin-button,
    #edit_quantity::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[name="quantity[]"],
    #edit_quantity {
        -moz-appearance: textfield;
    }
</style>
<div class="container">
    <div class="row">
        <div class="col-lg-10">
            <h3 class="text-center pb-4 mt-3">Material Estimation Request</h3>
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

                <form id="materialEstimationForm" action="{{ route('materialEstimation.add') }}" method="POST" class="container">
                    @csrf
                    <input type="hidden" name="site_id" value="{{ $site->id }}">

                    <h5 class="mb-3">Particulars</h5>

                    <div id="particularRows">
                        <div class="row mb-2 particular-row align-items-center">
                            <div class="col-md-4">
                                <input type="text" name="particular[]" class="form-control" placeholder="Particular (e.g. Bricks)" required>
                            </div>
                            <div class="col-md-2">
                                <input type="number" step="0.01" min="0" name="quantity[]" class="form-control" placeholder="Quantity">
                            </div>
                            <div class="col-md-2">
                                <select name="unit[]" class="form-control">
                                    <option value="">Select Unit</option>
                                    @foreach ($sharedUnits as $unitOption)
                                        <option value="{{ $unitOption }}">{{ $unitOption }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="date[]" class="form-control" required>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger remove-row">X</button>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-secondary my-3" id="addRow">+ Add Row</button>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4">Submit</button>
                    </div>
                </form>

                <hr class="mt-4 mb-3">

                <h5 class="mb-3">Requested Materials</h5>

                @if ($requests->isEmpty())
                    <p class="text-muted">No material estimation requests yet.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Particular</th>
                                    <th>Quantity</th>
                                    <th>Unit</th>
                                    <th>Date</th>
                                    <th style="width:100px">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($requests as $index => $req)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $req->particular }}</td>
                                        <td>{{ $req->quantity ?? '-' }}</td>
                                        <td>{{ $req->unit ?? '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($req->date)->format('d-m-Y') }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-1">
                                                <button type="button" class="btn btn-link btn-primary btn-sm editRequestBtn"
                                                    data-id="{{ $req->id }}"
                                                    data-particular="{{ $req->particular }}"
                                                    data-quantity="{{ $req->quantity }}"
                                                    data-unit="{{ $req->unit }}"
                                                    data-date="{{ \Carbon\Carbon::parse($req->date)->format('Y-m-d') }}"
                                                    data-bs-toggle="modal" data-bs-target="#editRequestModal" title="Edit">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-link btn-danger btn-sm deleteRequestBtn"
                                                    data-id="{{ $req->id }}"
                                                    data-bs-toggle="modal" data-bs-target="#deleteRequestModal" title="Delete">
                                                    <i class="fa fa-times"></i>
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

<!-- Edit Request Modal -->
<div class="modal fade" id="editRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editRequestForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Material Estimation Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Particular</label>
                        <input type="text" name="particular" id="edit_particular" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" step="0.01" min="0" name="quantity" id="edit_quantity" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unit</label>
                        <select name="unit" id="edit_unit" class="form-control">
                            <option value="">Select Unit</option>
                            @foreach ($sharedUnits as $unitOption)
                                <option value="{{ $unitOption }}">{{ $unitOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" id="edit_date" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deleteRequestForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this material estimation request?</p>
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
    const particularRows = document.getElementById('particularRows');
    const addRowBtn = document.getElementById('addRow');

    addRowBtn.addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'row mb-2 particular-row align-items-center';
        row.innerHTML = `
            <div class="col-md-4">
                <input type="text" name="particular[]" class="form-control" placeholder="Particular (e.g. Bricks)" required>
            </div>
            <div class="col-md-2">
                <input type="number" step="0.01" min="0" name="quantity[]" class="form-control" placeholder="Quantity">
            </div>
            <div class="col-md-2">
                <select name="unit[]" class="form-control">
                    <option value="">Select Unit</option>
                    @foreach ($sharedUnits as $unitOption)
                        <option value="{{ $unitOption }}">{{ $unitOption }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="date[]" class="form-control" required>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger remove-row">X</button>
            </div>
        `;
        particularRows.appendChild(row);
    });

    particularRows.addEventListener('click', function (e) {
        const removeBtn = e.target.closest('.remove-row');
        if (removeBtn && particularRows.querySelectorAll('.particular-row').length > 1) {
            removeBtn.closest('.particular-row').remove();
        }
    });

    // Edit / Delete buttons for the requested materials list
    document.addEventListener('click', function (e) {
        const editBtn = e.target.closest('.editRequestBtn');
        const deleteBtn = e.target.closest('.deleteRequestBtn');

        if (editBtn) {
            const id = editBtn.getAttribute('data-id');
            const form = document.getElementById('editRequestForm');
            form.action = '{{ url("admin/material-estimation-update") }}/' + id;
            document.getElementById('edit_particular').value = editBtn.getAttribute('data-particular') || '';
            document.getElementById('edit_quantity').value = editBtn.getAttribute('data-quantity') || '';
            document.getElementById('edit_unit').value = editBtn.getAttribute('data-unit') || '';
            document.getElementById('edit_date').value = editBtn.getAttribute('data-date') || '';
        }

        if (deleteBtn) {
            const id = deleteBtn.getAttribute('data-id');
            const form = document.getElementById('deleteRequestForm');
            form.action = '{{ url("admin/material-estimation-delete") }}/' + id;
        }
    });
});
</script>

@endsection
