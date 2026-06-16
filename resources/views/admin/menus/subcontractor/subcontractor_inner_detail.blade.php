@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                    <h3 class="fw-bold mb-3">{{ ucfirst($subcontractorType) }} Details</h3>
                    <ul class="breadcrumbs mb-0">
                        <li class="nav-home">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="icon-home"></i>
                            </a>
                        </li>
                        <li class="separator">
                            <i class="icon-arrow-right"></i>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('sitemanagement.list') }}">Site</a>
                        </li>
                        <li class="separator">
                            <i class="icon-arrow-right"></i>
                        </li>
                        <li class="nav-item">
                            <a href="#">{{ ucfirst($subcontractorType) }} Details</a>
                        </li>
                    </ul>
                </div>
                
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <h6 class="card-title mb-0 fw-bold">Site Name: {{ $siteName }}</h6>
                                </div>
                            </div>
                            <div class="row mb-2 align-items-end pb-3"
                                style="border-bottom: 1px solid rgb(235, 236, 236) !important;">
                                <div class="col-md-2">
                                    <input type="month" id="monthPicker" class="form-control">
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center fw-bold">
                                        <span class="badge badge-black week-btn" data-week="1">Week 1</span>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center fw-bold">
                                        <span class="badge badge-black week-btn" data-week="2">Week 2</span>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center fw-bold">
                                        <span class="badge badge-black week-btn" data-week="3">Week 3</span>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center fw-bold">
                                        <span class="badge badge-black week-btn" data-week="4">Week 4</span>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mt-3">
                                <h4 class="card-title" style="margin-right: 360px;">{{ ucfirst($subcontractorType) }} Overview
                                </h4>
                                @php
                                    $exportUrl = route('subcontractor.export.type', ['siteId' => $siteId, 'subcontractorType' => $subcontractorType]);
                                    $query = request()->getQueryString();
                                    if (!empty($query)) $exportUrl .= '?' . $query;
                                @endphp
                                <div class="me-2">
                                    <a href="{{ $exportUrl }}" class="btn btn-outline-secondary">Export</a>
                                </div>
                                <div class="col-md-2" style="margin-left: 20px">
                                    <a href="{{ route('subcontractor.serviceForm', ['siteId' => $siteId, 'subcontractorType' => $subcontractorType]) }}" class="btn btn-primary w-100">Add Service</a>
                                </div>
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

                        @if ($subcontractors->isEmpty())
                            <p class="text-center mt-3"> No {{ ucfirst($subcontractorType) }} list found this Site.</p>
                        @else
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="add-row" class="display table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Date</th>
                                                <th>Subcontractor</th>
                                                <th>Amount</th>
                                                <th>Remarks</th>
                                                <th style="width:10%">Action</th>
                                                {{-- <th>Available</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody id="bricksTableBody">
                                            @foreach ($subcontractors as $index => $sub)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $sub->date }}</td>
                                                    <td>{{ $sub->subcontractor->name }}</td>
                                                    <td>{{ $sub->amount }}</td>
                                                    <td>{{ $sub->remarks ?? '-' }}</td>
                                                    <td>
                                                        <div class="form-button-action">
                                                            <button type="button" class="btn btn-link btn-primary btn-sm editServiceBtn"
                                                                data-id="{{ $sub->id }}"
                                                                data-date="{{ $sub->date }}"
                                                                data-amount="{{ $sub->amount }}"
                                                                data-bs-toggle="modal" data-bs-target="#editServiceModal">
                                                                <i class="fa fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-link btn-danger btn-sm deleteServiceBtn"
                                                                data-id="{{ $sub->id }}" data-bs-toggle="modal" data-bs-target="#deleteServiceModal">
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
                    <!-- Edit Service Modal -->
                    <div class="modal fade" id="editServiceModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form id="editServiceForm" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Service</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Date</label>
                                            <input type="date" name="date" id="edit_service_date" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Amount</label>
                                            <input type="number" step="0.01" name="amount" id="edit_service_amount" class="form-control" required>
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

                    <!-- Delete Service Modal -->
                    <div class="modal fade" id="deleteServiceModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form id="deleteServiceForm" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Confirm Delete</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Are you sure you want to delete this service?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body d-flex justify-content-center">
                            <table class="table mt-3" style="width: 50%">
                                <tbody>
                                    <tr>
                                        <td>
                                            <h5 class="fw-bold text-info">TOTAL AMOUNT</h5>
                                        </td>
                                        <td>
                                            <h5 class="fw-bold text-info" id="totalUnits">{{ $totalAmount }}</h5>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const monthPicker = document.getElementById('monthPicker');
        const weekButtons = document.querySelectorAll('.week-btn');
        const spinner = document.getElementById('loadingSpinner'); // Optional spinner element
        const tableBody = document.getElementById('bricksTableBody');
        let selectedWeek = 0;

        const currentMonth = new Date().toISOString().slice(0, 7);
        monthPicker.value = currentMonth;

        weekButtons.forEach((button, index) => {
            button.addEventListener('click', function() {
                selectedWeek = index + 1;
                weekButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                fetchData();
            });
        });

        monthPicker.addEventListener('change', function() {
            selectedWeek = 0;
            weekButtons.forEach(btn => btn.classList.remove('active'));
            fetchData();
        });

        function fetchData() {
            if (spinner) spinner.classList.remove('d-none');

            fetch(`{{ route('subcontractor.getData', ['siteId' => $siteId]) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        monthYear: monthPicker.value,
                        week: selectedWeek,
                        subcontractor_type: '{{ $subcontractorType }}'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (spinner) spinner.classList.add('d-none');
                    tableBody.innerHTML = '';
                    data.subcontractors.forEach((item, index) => {
                        tableBody.innerHTML += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${item.date}</td>
                                <td>${item.subcontractor.name}</td>
                                <td>${item.amount}</td>
                                <td>${item.remarks || '-'}</td>
                                <td>
                                    <div class="form-button-action">
                                        <button type="button" class="btn btn-link btn-primary btn-sm editServiceBtn"
                                            data-id="${item.id}"
                                            data-date="${item.date}"
                                            data-amount="${item.amount}"
                                            data-bs-toggle="modal" data-bs-target="#editServiceModal">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-link btn-danger btn-sm deleteServiceBtn"
                                            data-id="${item.id}" data-bs-toggle="modal" data-bs-target="#deleteServiceModal">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>`;
                    });

                    document.getElementById('totalUnits').textContent = data.totalAmount;
                })
                .catch(error => {
                    if (spinner) spinner.classList.add('d-none');
                    console.error('Error fetching subcontractor data:', error);
                });
        }

        // Initial load
        fetchData();
        // Event delegation for edit/delete buttons
        document.addEventListener('click', function (e) {
            const editBtn = e.target.closest('.editServiceBtn');
            const delBtn = e.target.closest('.deleteServiceBtn');

            if (editBtn) {
                const id = editBtn.getAttribute('data-id');
                const date = editBtn.getAttribute('data-date');
                const amount = editBtn.getAttribute('data-amount');
                const form = document.getElementById('editServiceForm');
                form.action = '/admin/subcontractor-service-update/' + id;
                document.getElementById('edit_service_date').value = date;
                document.getElementById('edit_service_amount').value = amount;
            }

            if (delBtn) {
                const id = delBtn.getAttribute('data-id');
                const form = document.getElementById('deleteServiceForm');
                form.action = '/admin/subcontractor-service-delete/' + id;
            }
        });
    });
</script>


    <style>
        .week-btn {
            cursor: pointer;
        }

        .week-btn.active {
            background-color: #007bff;
            color: white;
        }
    </style>
@endsection
