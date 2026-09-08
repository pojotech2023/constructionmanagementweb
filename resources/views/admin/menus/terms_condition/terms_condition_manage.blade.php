@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Terms &amp; Conditions</h3>
                <ul class="breadcrumbs mb-3">
                    <li class="nav-home">
                        <a href="{{ route('admin.dashboard') }}">
                            <i class="icon-home"></i>
                        </a>
                    </li>
                    <li class="separator"><i class="icon-arrow-right"></i></li>
                    <li class="nav-item"><a href="#">Terms &amp; Conditions</a></li>
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
                                <div>
                                    <h4 class="card-title mb-0">Terms &amp; Conditions Templates</h4>
                                    <p class="text-muted mb-0 mt-1">
                                        Add a title and its description here. In Quotation, Sales Bill and Purchase Bill,
                                        typing a matching title auto-fills the description as the Terms &amp; Conditions on the PDF.
                                    </p>
                                </div>
                                <button type="button" class="btn btn-primary text-nowrap" data-bs-toggle="modal" data-bs-target="#addTermsModal">
                                    <i class="fa fa-plus"></i> Add Template
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            @if ($termsConditions->isEmpty())
                                <p class="text-center mt-3">No terms &amp; conditions templates found. Please add one.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="display table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Title</th>
                                                <th>Description</th>
                                                <th style="width: 10%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($termsConditions as $termsCondition)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $termsCondition->title }}</td>
                                                    <td>{{ \Illuminate\Support\Str::limit($termsCondition->description, 120) }}</td>
                                                    <td>
                                                        <div class="form-button-action">
                                                            <button type="button" class="btn btn-link btn-primary btn-lg editButton"
                                                                data-id="{{ $termsCondition->id }}"
                                                                data-title="{{ $termsCondition->title }}"
                                                                data-description="{{ $termsCondition->description }}"
                                                                data-bs-toggle="modal" data-bs-target="#editTermsModal">
                                                                <i class="fa fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-link btn-danger deleteButton"
                                                                data-id="{{ $termsCondition->id }}" data-bs-toggle="modal"
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

    <!-- Add Terms & Conditions Modal -->
    <div class="modal fade" id="addTermsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('terms-condition.add') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Terms &amp; Conditions Template</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" name="title" id="title" class="form-control"
                                placeholder="e.g. Standard Payment Terms" required>
                        </div>
                        <div class="form-group mt-3">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="5"
                                placeholder="This text will appear as Terms & Conditions in the PDF" required></textarea>
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

    <!-- Edit Terms & Conditions Modal -->
    <div class="modal fade" id="editTermsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="editTermsForm" action="" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Terms &amp; Conditions Template</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_title">Title</label>
                            <input type="text" name="title" id="edit_title" class="form-control" required>
                        </div>
                        <div class="form-group mt-3">
                            <label for="edit_description">Description</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="5" required></textarea>
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
                    Are you sure you want to delete this template?
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
                var title = $(this).data('title');
                var description = $(this).data('description');
                var action = "{{ route('terms-condition.update', ':id') }}".replace(':id', id);
                $('#editTermsForm').attr('action', action);
                $('#edit_title').val(title);
                $('#edit_description').val(description);
            });

            $('.deleteButton').click(function() {
                var id = $(this).data('id');
                var action = "{{ route('terms-condition.delete', ':id') }}".replace(':id', id);
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

