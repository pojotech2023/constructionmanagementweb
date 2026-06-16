@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Subcontractor</h3>
                <ul class="breadcrumbs mb-3">
                    <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
                    <li class="separator"><i class="icon-arrow-right"></i></li>
                    <li class="nav-item"><a href="#">Subcontractor Management</a></li>
                </ul>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <h4 class="card-title mb-0">Subcontractor Management</h4>
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
                            <button class="btn btn-primary btn-round ms-auto" id="addButton" data-bs-toggle="modal"
                                data-bs-target="#addModal">
                                <i class="fa fa-plus"></i> Add Subcontractor
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

                        @if ($subcontractors->isEmpty())
                            <p class="text-center mt-3">No Subcontractors found. Please add a Subcontractor.</p>
                        @else
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="add-row" class="display table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Name</th>
                                                <th>Subcontractors</th>
                                                <th>Whatsapp Number</th>
                                                <th>Email</th>
                                                <th>Address</th>
                                                <th>GST</th>
                                                <th style="width: 10%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($subcontractors as $index =>$subcontractor)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{$subcontractor->name }}</td>
                                                    <td>{{$subcontractor->subcontractors }}</td>
                                                    <td>{{$subcontractor->mobile_no }}</td>
                                                    <td>{{$subcontractor->email }}</td>
                                                    <td>{{$subcontractor->address }}</td>
                                                    <td>{{$subcontractor->gst }}</td>
                                                    <td>
                                                        <div class="form-button-action">
                                                            <!-- Edit Button -->
                                                            <button type="button"
                                                                class="btn btn-link btn-primary btn-lg editButton"
                                                                data-id="{{$subcontractor->id }}"
                                                                data-name="{{$subcontractor->name }}"
                                                                data-subcontractors="{{$subcontractor->subcontractors }}"
                                                                data-mobile="{{$subcontractor->mobile_no }}"
                                                                data-email="{{$subcontractor->email }}"
                                                                data-address="{{$subcontractor->address }}"
                                                                data-gst="{{$subcontractor->gst }}"
                                                                data-bs-toggle="modal" data-bs-target="#addModal">
                                                                <i class="fa fa-edit"></i>
                                                            </button>
                                                            <!-- Delete Button -->
                                                            <button type="button"
                                                                class="btn btn-link btn-danger deleteButton"
                                                                data-id="{{$subcontractor->id }}" data-bs-toggle="modal"
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
                        <span class="fw-mediumbold" id="modalTitle">Add Subcontractor</span>
                    </h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form id="subcontractorForm" action="{{ route('subcontractor.add') }}" method="POST">
                        @csrf
                        <input type="hidden" id="subcontractor_id" name="subcontractor_id">

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

                        <!-- Site Utilities -->
                        <div class="row align-items-center mb-3">
                            <div class="col-lg-2">
                                <label for="subcontractors">Subcontractors</label>
                            </div>
                            <div class="col-lg-10">
                                <div class="form-group">
                                    <select class="form-select form-control" name="subcontractors" id="subcontractors">
                                        <option value="">Select Subcontractor</option>
                                        <option value="Plumbers">Plumber</option>
                                        <option value="Electrician">Electrician</option>
                                        <option value="Painter">Painter</option>
                                        <option value="Welder">Welder</option>
                                        <option value="Tiles Layer">Tiles Layer</option>
                                        <option value="Granite Layer">Granite Layer</option>
                                        <option value="SS Welder">SS Welder</option>
                                        <option value="Carpenter">Carpenter</option>
                                        <option value="Centering Works">Centering Works</option>
                                        <option value="Mason Works">Mason Works</option>
                                    </select>
                                </div>
                                @error('subcontractors')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Mobile -->
                        <div class="row align-items-center mb-3">
                            <div class="col-lg-2">
                                <label for="mobile_no">Whatsapp Number</label>
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

                        <!-- Location -->
                        <div class="row align-items-center mb-3">
                            <div class="col-lg-2">
                                <label for="address">Address</label>
                            </div>
                            <div class="col-lg-10">
                                <textarea id="address" name="address" class="form-control" rows="2" placeholder="Enter Address"></textarea>
                                @error('address')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror

                            </div>
                        </div>

                        <!-- Gst -->
                        <div class="row align-items-center mb-3">
                            <div class="col-lg-2">
                                <label for="gst">GST</label>
                            </div>
                            <div class="col-lg-10">
                                <input id="gst" name="gst" type="text" class="form-control"
                                    placeholder="Enter gst" />
                                @error('gst')
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
            const subcontractorForm = document.getElementById("subcontractorForm");
            const subcontractorIdInput = document.getElementById("subcontractor_id");
            const nameInput = document.getElementById("name");
            const siteInput = document.getElementById("subcontractors");
            const mobileInput = document.getElementById("mobile");
            const emailInput = document.getElementById("email");
            const addressInput = document.getElementById("address");
            const gstInput = document.getElementById('gst');
            const saveButton = document.getElementById("saveButton");
            const spinner = document.getElementById("loadingSpinner");

            // Add vendor Button Click
            document.getElementById("addButton").addEventListener("click", function() {
                modalTitle.innerText = "Add Vendor";
                saveButton.innerText = "Add";
                subcontractorForm.action = "{{ route('subcontractor.add') }}";
                subcontractorIdInput.value = "";
                nameInput.value = "";
                siteInput.value = "";
                mobileInput.value = "";
                emailInput.value = "";
                addressInput.value = "";
                gstInput.value = "";
            });

            // Edit vendor Button Click
            document.querySelectorAll(".editButton").forEach(button => {
                button.addEventListener("click", function() {
                    const subcontractorId = this.getAttribute("data-id");
                    const vendorName = this.getAttribute("data-name");
                    const vendorSite = this.getAttribute("data-subcontractors");
                    const vendorMobile = this.getAttribute("data-mobile");
                    const vendorEmail = this.getAttribute("data-email");
                    const vendorAddress = this.getAttribute("data-address");
                    const vendorGst = this.getAttribute("data-gst");

                    modalTitle.innerText = "Edit Vendor";
                    saveButton.innerText = "Update";
                    subcontractorForm.action = "{{ route('subcontractor.update') }}";
                    subcontractorIdInput.value = subcontractorId;
                    nameInput.value = vendorName;
                    siteInput.value = vendorSite;
                    mobileInput.value = vendorMobile;
                    emailInput.value = vendorEmail;
                    addressInput.value = vendorAddress;
                    gstInput.value = vendorGst;
                });
            });

            // Delete Button Click
            document.querySelectorAll(".deleteButton").forEach(button => {
                button.addEventListener("click", function() {
                    const subcontractorId = this.getAttribute("data-id");
                    const action = "{{ route('subcontractor.delete', ':id') }}".replace(':id',
                        subcontractorId);
                    document.getElementById("deleteForm").setAttribute("action", action);
                });
            });

            //Show Spinner and Disable Form on Submit
            subcontractorForm.addEventListener("submit", function() {
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
                siteInput.value = "";
                mobileInput.value = "";
                emailInput.value = "";
                addressInput.value = "";
                gstInput.value = "";

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
