@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Supervisor Creation</h3>
                <ul class="breadcrumbs mb-3">
                    <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
                    <li class="separator"><i class="icon-arrow-right"></i></li>
                    <li class="nav-item"><a href="#">Supervisor</a></li>
                    <li class="separator"><i class="icon-arrow-right"></i></li>
                    <li class="nav-item"><a href="#">Supervisor Creation</a></li>
                </ul>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <h4 class="card-title mb-0">Supervisor Creation</h4>
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
                            <div class="ms-auto d-flex align-items-center gap-2">
                                <a href="{{ route('supervisor.locations') }}" class="btn btn-info btn-round">
                                    <i class="fa fa-map-marked-alt"></i> View All Locations
                                </a>
                                <button class="btn btn-primary btn-round" id="addButton" data-bs-toggle="modal"
                                    data-bs-target="#addModal">
                                    <i class="fa fa-plus"></i> Add Supervisor
                                </button>
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

                        @if ($supervisors->isEmpty())
                            <p class="text-center mt-3">No Supervisor found. Please add a supervisor user.</p>
                        @else
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="add-row" class="display table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Name</th>
                                                <th>Mobile Number</th>
                                                <th>Email</th>
                                                <th style="width: 10%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($supervisors as $index => $supervisor)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $supervisor->name }}</td>
                                                    <td>{{ $supervisor->mobile_no }}</td>
                                                    <td>{{ $supervisor->email }}</td>
                                                    <td>
                                                        <div class="form-button-action">
                                                            <!-- Edit Button -->
                                                            <button type="button"
                                                                class="btn btn-link btn-primary btn-lg editButton"
                                                                data-id="{{ $supervisor->id }}"
                                                                data-name="{{ $supervisor->name }}"
                                                                data-mobile="{{ $supervisor->mobile_no }}"
                                                                data-email="{{ $supervisor->email }}"
                                                                data-password="{{ $supervisor->password }}"
                                                                data-bs-toggle="modal" data-bs-target="#addModal">
                                                                <i class="fa fa-edit"></i>
                                                            </button>
                                                            <!-- Permission Button -->
                                                            <a href="{{ route('supervisor.permissions.get', $supervisor->id) }}"
                                                                class="btn btn-link btn-success"
                                                                title="Permissions">
                                                                <i class="fa fa-key"></i>
                                                            </a>
                                                            <!-- Location Button -->
                                                            <a href="{{ route('supervisor.location', $supervisor->id) }}"
                                                                class="btn btn-link btn-info"
                                                                title="View Location">
                                                                <i class="fa fa-map-marker-alt"></i>
                                                            </a>
                                                            <!-- Delete Button -->
                                                            <button type="button"
                                                                class="btn btn-link btn-danger deleteButton"
                                                                data-id="{{ $supervisor->id }}" data-bs-toggle="modal"
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

    <!-- Add/Edit Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">
                        <span class="fw-mediumbold" id="modalTitle">Add Supervisor</span>
                    </h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form id="supervisorForm" action="{{ route('supervisor.add') }}" method="POST">
                        @csrf
                        <input type="hidden" id="user_id" name="user_id">

                        <!-- Name -->
                        <div class="row align-items-center mb-3">
                            <div class="col-lg-2">
                                <label for="name">Name</label>
                            </div>
                            <div class="col-lg-10">
                                <input id="name" name="name" type="text" class="form-control"
                                    placeholder="Enter name" />
                                @error('name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Mobile -->
                        <div class="row align-items-center mb-3">
                            <div class="col-lg-2">
                                <label for="mobile_no">Mobile Number</label>
                            </div>
                            <div class="col-lg-10">
                                <input id="mobile" name="mobile_no" type="text" class="form-control"
                                    placeholder="Enter mobile number" maxlength="10" minlength="10" pattern="\d{10}"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);"/>
                                @error('mobile_no')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="row align-items-center mb-3">
                            <div class="col-lg-2">
                                <label for="email">Email Id</label>
                            </div>
                            <div class="col-lg-10">
                                <input id="email" name="email" type="email" class="form-control"
                                    placeholder="Enter email" />
                                @error('email')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="row align-items-center mb-3">
                            <div class="col-lg-2">
                                <label for="password">Password</label>
                            </div>
                            <div class="col-lg-10">
                                <input id="password" name="password" type="password" class="form-control"
                                    placeholder="Enter password" />
                                @error('password')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="row align-items-center mb-3">
                            <div class="col-lg-2">
                                <label for="password_confirmation">Confirm Password</label>
                            </div>
                            <div class="col-lg-10">
                                <input id="password_confirmation" name="password_confirmation" type="password"
                                    class="form-control" placeholder="Re-enter password" />
                            </div>
                             @error('password_confirmation')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                        </div>


                        <div class="modal-footer border-0">
                            <button type="submit" class="btn btn-primary" id="saveButton">Add</button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this record?
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" method="POST">
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
            const modalTitle = document.getElementById("modalTitle");
            const supervisorForm = document.getElementById("supervisorForm");
            const supervisorIdInput = document.getElementById("user_id");
            const nameInput = document.getElementById("name");
            const mobileInput = document.getElementById("mobile");
            const emailInput = document.getElementById("email");
            const saveButton = document.getElementById("saveButton");
            const spinner = document.getElementById("loadingSpinner");

            // Add vendor Button Click
            document.getElementById("addButton").addEventListener("click", function() {
                modalTitle.innerText = "Add Supervisor";
                saveButton.innerText = "Add";
                supervisorForm.action = "{{ route('supervisor.add') }}";
                supervisorIdInput.value = "";
                nameInput.value = "";
                mobileInput.value = "";
                emailInput.value = "";
            });

            // Edit vendor Button Click
            document.querySelectorAll(".editButton").forEach(button => {
                button.addEventListener("click", function() {
                    const supervisorId = this.getAttribute("data-id");
                    const supervisorName = this.getAttribute("data-name");
                    const supervisorMobile = this.getAttribute("data-mobile");
                    const supervisorEmail = this.getAttribute("data-email");

                    modalTitle.innerText = "Edit Supervisor";
                    saveButton.innerText = "Update";
                    supervisorForm.action = "{{ route('supervisor.update') }}";
                    supervisorIdInput.value = supervisorId;
                    nameInput.value = supervisorName;
                    mobileInput.value = supervisorMobile;
                    emailInput.value = supervisorEmail;

                     mobileInput.readOnly = true;
                });
            });

            // Delete Button Click
            document.querySelectorAll(".deleteButton").forEach(button => {
                button.addEventListener("click", function() {
                    const supervisorId = this.getAttribute("data-id");
                    const action = "{{ route('supervisor.delete', ':id') }}".replace(':id',
                        supervisorId);
                    document.getElementById("deleteForm").setAttribute("action", action);
                });
            });

            //Show Spinner and Disable Form on Submit
            supervisorForm.addEventListener("submit", function() {
                spinner.classList.remove("d-none"); // Show spinner
                saveButton.disabled = true; // Disable button to prevent multiple clicks
            });

            //Auto-hide success alert after 3 seconds
            const successAlert = document.querySelector(".alert-success");
            if (successAlert) {
                setTimeout(() => {
                    successAlert.classList.remove("show");
                    successAlert.classList.add("fade");
                }, 500);
            }

            //Clear validation error when modal is closed
            addModal.addEventListener('hidden.bs.modal', function() {
                // Clear form fields
                nameInput.value = "";
                mobileInput.value = "";
                emailInput.value = "";

                // Remove error messages manually
                const errorMessages = addModal.querySelectorAll('.text-danger');
                errorMessages.forEach(el => el.remove());

                // Remove is-invalid class
                const errorInputs = addModal.querySelectorAll('.is-invalid');
                errorInputs.forEach(input => input.classList.remove('is-invalid'));
            });
        });
    </script>
    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var myModal = new bootstrap.Modal(document.getElementById('addModal'));
                myModal.show();
            });
        </script>
    @endif
@endsection
