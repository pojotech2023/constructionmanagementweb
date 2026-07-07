@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Add Checklist</h3>
                <ul class="breadcrumbs mb-3">
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
                        <a href="#">Checklist Form</a>
                    </li>
                </ul>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between w-100">
                                <h4 class="card-title">Checklist Form</h4>
                            </div>
                        </div>
                        <div class="card-body">

                            <!-- Blade alert for success -->
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                                {{ session()->forget('success') }} {{-- Clear session --}}
                            @endif

                            <div class="row">
                                <form id="siteForm" action="{{ route('checklist.add') }}" method="POST"
                                    enctype="multipart/form-data" class="container">
                                    @csrf
                                    <!-- Site Name -->
                                    <div class="row align-items-center mt-5">
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="site_name">Stages</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-10">
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="stage">
                                            </div>
                                            @error('stage')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                        <!-- Site Images -->
                                         <div class="row">
                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label for="site_img">Task List</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-10">
                                        <div id="file-upload-wrapper">
                                        <div class="form-group file-group mb-2 task-field-row">
                                        <input type="text" name="task_list[]" class="form-control">
                                        <button type="button" class="task-remove-btn remove-btn" aria-label="Remove task">&times;</button>
                                        </div>
                                        </div>
                                        <button type="button" class="btn btn-primary btn-sm mt-1" id="add-file-button">+ Add another file</button>

                                        @error('task_list')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                        </div>

                                    <div class="card-action text-end">
                                        <button type="submit" class="btn btn-success" id="saveButton">Submit</button>
                                        <button type="button" class="btn btn-danger" onclick="window.history.back()">Cancel</button>
                                    </div>
                                </form>
                            </div>
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
        document.addEventListener("DOMContentLoaded", function() {
            const siteForm = document.getElementById('siteForm');
            const saveButton = document.getElementById("saveButton");
            const spinner = document.getElementById("loadingSpinner");

            //Show Spinner and Disable Form on Submit
            siteForm.addEventListener("submit", function() {
                spinner.classList.remove("d-none"); // Show spinner
                saveButton.disabled = true; // Disable button to prevent multiple clicks
            });

            //Auto-hide success alert after 3 seconds
            const successAlert = document.querySelector(".alert-success");
            if (successAlert) {
                setTimeout(() => {
                    successAlert.classList.remove("show");
                    successAlert.classList.add("fade");
                    window.location.href = "/checklist-add";

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

    <script>
document.getElementById('add-file-button').addEventListener('click', function () {
    const wrapper = document.getElementById('file-upload-wrapper');

    const newField = document.createElement('div');
    newField.classList.add('form-group', 'file-group', 'mb-2', 'task-field-row');

    newField.innerHTML = `
        <input type="text" name="task_list[]" class="form-control">
        <button type="button" class="task-remove-btn remove-btn" aria-label="Remove task">&times;</button>
    `;

    wrapper.appendChild(newField);
});

// Remove a task field, keeping at least one field on the form
document.addEventListener('click', function (e) {
    if (e.target && e.target.classList.contains('remove-btn')) {
        const wrapper = document.getElementById('file-upload-wrapper');
        if (wrapper.querySelectorAll('.task-field-row').length > 1) {
            e.target.closest('.task-field-row').remove();
        } else {
            e.target.closest('.task-field-row').querySelector('input').value = '';
        }
    }
});
</script>

<style>
    .task-field-row {
        position: relative;
    }

    .task-field-row .form-control {
        padding-right: 40px;
    }

    .task-remove-btn {
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        width: 24px;
        height: 24px;
        line-height: 22px;
        padding: 0;
        border: none;
        border-radius: 50%;
        background: #dc3545;
        color: #fff;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
    }

    .task-remove-btn:hover {
        background: #b52a37;
    }
</style>

@endsection
