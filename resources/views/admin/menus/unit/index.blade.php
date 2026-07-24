@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Unit Master</h3>
                <ul class="breadcrumbs mb-3">
                    <li class="nav-home">
                        <a href="{{ route('admin.dashboard') }}">
                            <i class="icon-home"></i>
                        </a>
                    </li>
                    <li class="separator"><i class="icon-arrow-right"></i></li>
                    <li class="nav-item"><a href="#">Unit Master</a></li>
                </ul>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <h4 class="card-title mb-0">Unit List</h4>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUnitModal">
                                    <i class="fa fa-plus"></i> Add Unit
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            @if ($units->isEmpty())
                                <p class="text-center mt-3">No units found. Please add a unit.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="display table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Unit Name</th>
                                                <th style="width: 10%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($units as $unit)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $unit->name }}</td>
                                                    <td>
                                                        <div class="form-button-action">
                                                            <button type="button" class="btn btn-link btn-primary btn-lg editButton"
                                                                data-id="{{ $unit->id }}" data-name="{{ $unit->name }}"
                                                                data-bs-toggle="modal" data-bs-target="#editUnitModal">
                                                                <i class="fa fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-link btn-danger deleteButton"
                                                                data-id="{{ $unit->id }}" data-bs-toggle="modal"
                                                                data-bs-target="#deleteModal">
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
        </div>
    </div>

    <!-- Add Unit Modal -->
    <div class="modal fade" id="addUnitModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('unit.add') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Unit</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Unit Name</label>
                            <input type="text" name="name" id="name" class="form-control" placeholder="e.g. Kg, Bag, Ton" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Unit Modal -->
    <div class="modal fade" id="editUnitModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="editUnitForm" action="" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Unit</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_name">Unit Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this unit?
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" action="" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            $('.editButton').click(function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                var action = "{{ route('unit.update', ':id') }}".replace(':id', id);
                $('#editUnitForm').attr('action', action);
                $('#edit_name').val(name);
            });

            $('.deleteButton').click(function() {
                var id = $(this).data('id');
                var action = "{{ route('unit.delete', ':id') }}".replace(':id', id);
                $('#deleteForm').attr('action', action);
            });

            const successAlert = document.querySelector(".alert-success");
            if (successAlert) {
                setTimeout(() => {
                    successAlert.classList.remove("show");
                    successAlert.classList.add("fade");
                }, 3000);
            }
        });
    </script>
@endsection
