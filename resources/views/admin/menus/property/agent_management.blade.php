@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Agent</h3>
                <ul class="breadcrumbs mb-3">
                    <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
                    <li class="separator"><i class="icon-arrow-right"></i></li>
                    <li class="nav-item"><a href="#">Agent</a></li>
                    <li class="separator"><i class="icon-arrow-right"></i></li>
                    <li class="nav-item"><a href="#">Agent Management</a></li>
                </ul>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <h4 class="card-title mb-0">Agent Management</h4>
                            </div>
                            <button class="btn btn-primary btn-round ms-auto" id="addButton" data-bs-toggle="modal"
                                data-bs-target="#addModal">
                                <i class="fa fa-plus"></i> Add Real Estate Agent
                            </button>
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

                        @if ($agents->isEmpty())
                            <p class="text-center mt-3">No Agents found. Please add Agent.</p>
                        @else
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="add-row" class="display table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Name</th>
                                                <th>Company Name</th>
                                                <th>Mobile</th>
                                                <th style="width: 10%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($agents as $index => $agent)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $agent->name }}</td>
                                                    <td>{{ $agent->company_name }}</td>
                                                    <td>{{ $agent->mobile_no }}</td>
                                                    <td>
                                                        <div class="form-button-action">
                                                            <!-- Edit Button -->
                                                            <button type="button"
                                                                class="btn btn-link btn-primary btn-lg editButton"
                                                                data-id="{{ $agent->id }}"
                                                                data-name="{{ $agent->name }}"
                                                                data-company-name="{{ $agent->company_name }}"
                                                                data-mobile="{{ $agent->mobile_no }}" data-bs-toggle="modal"
                                                                data-bs-target="#addModal">
                                                                <i class="fa fa-edit"></i>
                                                            </button>
                                                            <!-- Delete Button -->
                                                            <button type="button"
                                                                class="btn btn-link btn-danger deleteButton"
                                                                data-id="{{ $agent->id }}" data-bs-toggle="modal"
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
                        <span class="fw-mediumbold" id="modalTitle">Add Agent</span>
                    </h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form id="agentForm" action="{{ route('agent.add') }}" method="POST">
                        @csrf
                        <input type="hidden" id="agent_id" name="agent_id">

                        <!-- Name -->
                        <div class="row align-items-center mb-3">
                            <div class="col-lg-4">
                                <label for="name">Name</label>
                            </div>
                            <div class="col-lg-8">
                                <input id="name" name="name" type="text" class="form-control"
                                    placeholder="Enter name" />
                                @error('name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Company Name -->
                        <div class="row align-items-center mb-3">
                            <div class="col-lg-4">
                                <label for="company_name">Company Name</label>
                            </div>
                            <div class="col-lg-8">
                                <input id="company_name" name="company_name" type="text" class="form-control"
                                    placeholder="Enter Company Name" />
                                @error('company_name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Mobile -->
                        <div class="row align-items-center mb-3">
                            <div class="col-lg-4">
                                <label for="mobile_no">Mobile Number</label>
                            </div>
                            <div class="col-lg-8">
                                <input id="mobile" name="mobile_no" type="text" class="form-control"
                                    placeholder="Enter mobile number" maxlength="10" minlength="10" pattern="\d{10}"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);" />
                                @error('mobile_no')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
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
                        @method('PATCH')
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
            const agentForm = document.getElementById("agentForm");
            const agentIdInput = document.getElementById("agent_id");
            const nameInput = document.getElementById("name");
            const companyNameInput = document.getElementById("company_name");
            const mobileInput = document.getElementById("mobile");
            const saveButton = document.getElementById("saveButton");
            const spinner = document.getElementById("loadingSpinner");

            // Add agent Button Click
            document.getElementById("addButton").addEventListener("click", function() {
                modalTitle.innerText = "Add Agent";
                saveButton.innerText = "Add";
                agentForm.action = "{{ route('agent.add') }}";
                agentIdInput.value = "";
                nameInput.value = "";
                companyNameInput.value = "";
                mobileInput.value = "";
            });

            // Edit agent Button Click
            document.querySelectorAll(".editButton").forEach(button => {
                button.addEventListener("click", function() {
                    const agentId = this.getAttribute("data-id");
                    const agentName = this.getAttribute("data-name");
                    const agentCompanyName = this.getAttribute("data-company-name");
                    const agentMobile = this.getAttribute("data-mobile");

                    modalTitle.innerText = "Edit Agent";
                    saveButton.innerText = "Update";
                    agentForm.action = "{{ route('agent.update') }}";
                    agentIdInput.value = agentId;
                    nameInput.value = agentName;
                    companyNameInput.value = agentCompanyName;
                    mobileInput.value = agentMobile;
                });
            });

            // Delete Button Click
            document.querySelectorAll(".deleteButton").forEach(button => {
                button.addEventListener("click", function() {
                    const agentId = this.getAttribute("data-id");
                    const action = "{{ route('agent.delete', ':id') }}".replace(':id',
                        agentId);
                    document.getElementById("deleteForm").setAttribute("action", action);
                });
            });

            //Show Spinner and Disable Form on Submit
            agentForm.addEventListener("submit", function() {
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
                companyNameInput.value = "";
                mobileInput.value = "";

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
