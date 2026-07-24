@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Drwaing Update</h3>
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
                        <a href="{{ route('site.detail', $site->id) }}">{{ $site->site_name }}</a>
                    </li>
                    <li class="separator">
                        <i class="icon-arrow-right"></i>
                    </li>
                    <li class="nav-item">
                        <a href="#">Drawing Update Form</a>
                    </li>
                </ul>
            </div>


<div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                    
                            <div class="d-flex align-items-center justify-content-between w-100">
                                <h4 class="card-title">Drawing Update Form</h4>
                               
                                {{---<p style="text-decoration: line-through;">Completed</p>--}}
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

                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li> {{-- Shows each error --}}
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif  

                            <div class="row">
                                <form action="{{ route('drawings.store') }}" method="POST" enctype="multipart/form-data" id="taskForm">
                                @csrf
                                <input type="hidden" name="site_id" value="{{ $site->id }}">

                                <div class="row mb-3">
                                    <label class="col-md-2 col-form-label fw-bold">Upload Files</label>
                                    <div class="col-md-10">
                                        <div id="file-upload-wrapper">
                                            <div class="input-group mb-2">
                                                <input type="file" name="attachments[]" class="form-control" multiple accept="image/*,application/pdf">
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-file-field">+ Add More</button>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                    <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
              </div>
                </div>
        
    </div>

@if($drawings->count())
    <hr>
    <h5 class="mt-4 mb-3 fw-bold">Uploaded Drawings</h5>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th style="width: 50px;">S.No</th>
                    <th>Drawing</th>
                    <th style="width: 100px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($drawings as $index => $drawing)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            @if(Str::contains($drawing->file_type, 'image'))
    <img src="{{ asset('storage/' . $drawing->file_path) }}" 
         alt="Drawing Image" 
         width="100" 
         height="100">
@elseif(Str::contains($drawing->file_type, 'pdf'))
    <a href="{{ asset('storage/' . $drawing->file_path) }}" 
       target="_blank">📄 View PDF</a>
@elseif(Str::contains($drawing->file_type, 'excel') || 
        Str::contains($drawing->file_type, 'spreadsheet') || 
        Str::contains($drawing->file_type, 'xls') || 
        Str::contains($drawing->file_type, 'xlsx'))
    <a href="{{ asset('storage/' . $drawing->file_path) }}" 
       download>📊 Download Excel</a>
@elseif(Str::contains($drawing->file_type, 'dwg') || 
        pathinfo($drawing->file_name, PATHINFO_EXTENSION) === 'dwg')
    <a href="{{ asset('storage/' . $drawing->file_path) }}" 
       download>📐 Download DWG</a>
@else
    <span>Unsupported file type</span>
@endif

                        </td>
                       <td>
    <div class="d-flex gap-2">
        <!-- Download Button -->
        <a href="{{ asset('storage/' . $drawing->file_path) }}" download class="btn btn-sm btn-primary">
            ⬇️ Download
        </a>

        <!-- WhatsApp Share Button -->
        <a href="#" class="btn btn-sm btn-success whatsappShareBtn"
           data-url="{{ asset('storage/' . $drawing->file_path) }}"
           data-name="{{ $drawing->file_name }}" title="Share on WhatsApp">
            📤 WhatsApp
        </a>

        <!-- Delete Button -->
        <form action="{{ route('drawings.destroy', $drawing->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger">🗑️ Delete</button>
        </form>
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

   
    <!-- Spinner -->
    <div class="d-flex justify-content-center mt-3">
        <div class="spinner-border text-primary d-none" role="status" id="loadingSpinner">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.addEventListener('click', function (e) {
                const shareBtn = e.target.closest('.whatsappShareBtn');
                if (!shareBtn) return;

                e.preventDefault();
                const url = shareBtn.getAttribute('data-url');
                const name = shareBtn.getAttribute('data-name') || 'Drawing';
                const message = `Drawing: ${name}\n${url}`;
                const whatsappUrl = 'https://wa.me/?text=' + encodeURIComponent(message);
                window.open(whatsappUrl, '_blank');
            });
        });
    </script>

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
                    window.location.href = "/admin/checklist";

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
    document.addEventListener('DOMContentLoaded', function () {
    const wrapper = document.getElementById('file-upload-wrapper');
    const addButton = document.getElementById('add-file-field');

    addButton.addEventListener('click', function () {
        const inputGroup = document.createElement('div');
        inputGroup.classList.add('input-group', 'mb-2');

        inputGroup.innerHTML = `
            <input type="file" name="attachments[]" class="form-control" accept="image/*,application/pdf">
            <button type="button" class="btn btn-danger btn-sm remove-file-field">🗑️</button>
        `;

        wrapper.appendChild(inputGroup);
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-file-field')) {
            e.target.closest('.input-group').remove();
        }
    });
});
 </script>
@endsection
