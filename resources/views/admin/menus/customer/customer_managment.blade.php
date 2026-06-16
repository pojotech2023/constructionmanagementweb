@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Customer Management</h3>
                <ul class="breadcrumbs mb-3">
                    <li class="nav-home">
                        <a href="{{ route('admin.dashboard') }}">
                            <i class="icon-home"></i>
                        </a>
                    </li>
                    <li class="separator"><i class="icon-arrow-right"></i></li>
                    <li class="nav-item"><a href="#">Customer Management</a></li>
                </ul>
            </div>

            {{-- reminder --}}
            @if (!empty($reminders) && count($reminders) > 0)
                <div class="alert alert-info mb-3">
                    <strong>Upcoming Reminders (Tomorrow):</strong>
                    <ul class="mb-0">
                        @foreach ($reminders as $reminder)
                            <li>
                                <strong>{{ $reminder['name'] }}</strong> - {{ $reminder['type'] }} on
                                {{ \Carbon\Carbon::parse($reminder['date'])->format('d M') }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif


            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center">
                                <h4 class="card-title">Customer List</h4>
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

                        @if ($customers->isEmpty())
                            <p class="text-center mt-3"> No Customer list found. Please create Customer.</p>
                        @else
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="add-row" class="display table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Name</th>
                                                <th>Mobile Number</th>
                                                <th>Email ID</th>
                                                <th>DOB</th>
                                                
                                                <th style="width: 10%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($customers as $index => $customer)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $customer->name }}</td>
                                                    <td>{{ $customer->mobile_no }}</td>
                                                    <td>{{ $customer->email }}</td>
                                                    <td>{{ $customer->dob }}</td>
                                                    
                                                    <td>
                                                        <div class="form-button-action">
                                                            <a href="{{ route('customer.edit', ['id' => $customer->id]) }}"
                                                                class="btn btn-link btn-primary btn-lg">
                                                                <i class="fa fa-edit"></i>
                                                            </a>
                                                            <button type="button"
                                                                class="btn btn-link btn-danger deleteButton"
                                                                data-id="{{ $customer->id }}" data-bs-toggle="modal"
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
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal (Outside Loop) -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this record?
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

    <!-- Spinner -->
    <div class="d-flex justify-content-center mt-3">
        <div class="spinner-border text-primary d-none" role="status" id="loadingSpinner">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            //delete customer
            $('.deleteButton').click(function() {
                var id = $(this).data('id');
                var action = "{{ route('customer.delete', ':id') }}".replace(':id', id);
                $('#deleteForm').attr('action', action);
            });

            //Auto-hide success alert after 3 seconds 
            const successAlert = document.querySelector(".alert-success");
            const form = document.getElementById('deleteForm');
            const spinner = document.getElementById('loadingSpinner');

            if (successAlert) {
                setTimeout(() => {
                    successAlert.classList.remove("show");
                    successAlert.classList.add("fade");
                }, 500);
            }
            //Show spinner only on site form submission
            if (form && spinner) {
                form.addEventListener('submit', function(event) {
                    spinner.classList.remove('d-none'); //Show spinner
                });
            }
        });
    </script>
@endsection
