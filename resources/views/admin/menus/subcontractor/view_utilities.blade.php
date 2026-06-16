@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Site</h3>
                <ul class="breadcrumbs mb-3">
                    <li class="nav-home"><a href="#"><i class="icon-home"></i></a></li>
                    <li class="separator"><i class="icon-arrow-right"></i></li>
                    <li class="nav-item">
                        <a href="{{ route('sitemanagement.list') }}">Site</a>
                    </li>
                    <li class="separator"><i class="icon-arrow-right"></i></li>
                    <li class="nav-item"><a href="#">Other Utilities</a></li>
                </ul>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <h4 class="card-title mb-0">Other Utilities</h4>
                                {{-- <form method="GET" action="{{ route('vendor-list') }}" class="d-flex align-items-center">
                                    <div class="input-group" style="width: 280px !important;">
                                        <span class="input-group-text">
                                            <i class="fas fa-search"></i>
                                        </span>
                                        <input type="text" name="search" value="{{ request('search') }}"
                                            class="form-control" id="searchLeads"
                                            placeholder="Search Name, Mobile, District...">
                                    </div>
                                </form> --}}
                            </div>
                        </div>

                        <!-- Blade alert for success -->
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                            {{ session()->forget('success') }} {{-- Clear session --}}
                        @endif

                        @if ($utilities->isEmpty())
                            <p class="text-center mt-3">No Other Utilities found. Please add a Others.</p>
                        @else
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="add-row" class="display table table-striped table-hover">
                                        <thead>
                                                <tr>
                                                    <th>S.No</th>
                                                    <th>Date</th>
                                                    <th>Amount</th>
                                                    <th>Remarks</th>
                                                    <th>Image</th>
                                                    <th style="width: 10%">Action</th>
                                                </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($utilities as $index => $utility)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($utility->created_at)->format('Y-m-d') }}</td>
                                                    <td>{{ $utility->amount }}</td>
                                                    <td>{{ $utility->remarks }}</td>
                                                    <td>
                                                        @if ($utility->image)
                                                            <img src="{{ asset('storage/' . $utility->image) }}" alt="Utility Image" width="75">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="form-button-action">
                                                            <button type="button"
                                                                class="btn btn-link btn-primary btn-lg editUtilityBtn"
                                                                data-id="{{ $utility->id }}"
                                                                data-amount="{{ $utility->amount }}"
                                                                data-remarks="{{ $utility->remarks }}"
                                                                data-date="{{ \Carbon\Carbon::parse($utility->created_at)->format('Y-m-d') }}"
                                                                data-bs-toggle="modal" data-bs-target="#editUtilityModal">
                                                                <i class="fa fa-edit"></i>
                                                            </button>
                                                            <button type="button"
                                                                class="btn btn-link btn-danger deleteUtilityBtn"
                                                                data-id="{{ $utility->id }}" data-bs-toggle="modal"
                                                                data-bs-target="#deleteUtilityModal">
                                                                <i class="fa fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal fade" id="editUtilityModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form id="editUtilityForm" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Others</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Amount</label>
                                    <input type="number" step="0.01" name="amount" id="edit_amount" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Date</label>
                                    <input type="date" name="date" id="edit_date" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Remarks</label>
                                    <textarea name="remarks" id="edit_remarks" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Image (optional)</label>
                                    <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
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

            <div class="modal fade" id="deleteUtilityModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form id="deleteUtilityForm" method="POST">
                            @csrf
                            @method('DELETE')
                            <div class="modal-header">
                                <h5 class="modal-title">Confirm Delete</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to delete this record?</p>
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
                    document.querySelectorAll('.editUtilityBtn').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            var id = this.getAttribute('data-id');
                            var amount = this.getAttribute('data-amount');
                            var remarks = this.getAttribute('data-remarks');
                            var date = this.getAttribute('data-date');
                            var form = document.getElementById('editUtilityForm');

                            form.action = '/admin/subutilities-update/' + id;
                            document.getElementById('edit_amount').value = amount;
                            document.getElementById('edit_date').value = date;
                            document.getElementById('edit_remarks').value = remarks;
                        });
                    });

                    document.querySelectorAll('.deleteUtilityBtn').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            var id = this.getAttribute('data-id');
                            document.getElementById('deleteUtilityForm').action = '/admin/subutilities-delete/' + id;
                        });
                    });
                });
            </script>
        </div>
    </div>
@endsection
