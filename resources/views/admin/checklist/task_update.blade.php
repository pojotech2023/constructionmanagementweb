@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Task Update</h3>
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
                        <a href="{{ url('admin/checklist/'.$site->id) }}">checklist</a>
                    </li>
                    <li class="separator">
                        <i class="icon-arrow-right"></i>
                    </li>
                    <li class="nav-item">
                        <a href="#">Task Update Form</a>
                    </li>
                </ul>
            </div>


<div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                        @if($latestMedia && $latestMedia->admin_remark)
    <h4>AdminRemark:</h4>
    <p style="color: #444; background: #f0f0f0; padding: 5px; border-radius: 4px;">
        {{ $latestMedia->admin_remark }}
    </p>
@else
    <p style="color: grey;"></p>
@endif

                            <div class="d-flex align-items-center justify-content-between w-100">
                                <h4 class="card-title">Task Update Form</h4>
                               
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
      <form id="" action="{{ route('task.upload.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="site_id" value="{{ $site->id }}">
                        <input type="hidden" name="task_id" value="{{ $task->id }}">

                        <div class="row align-items-center">
                            <div class="col-lg-2">
                                <label for="amount">Multiple Images Add</label>
                            </div>
                            <div class="col-lg-10">
                                <input  name="task_images[]" type="file" class="form-control no-arrow" min="0" step="1"
                                    placeholder="Enter Amount" accept="Image/*" multiple />
                            </div>
                            @error('amount')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row align-items-center mt-3">
                            <div class="col-lg-2">
                                <label for="image">Multiple VIdeos Add</label>
                            </div>
                            <div class="col-lg-10">
                                <input id="image" name="task_videos[]" type="file" class="form-control"
                                   accept="video/*" multiple/>
                            </div>
                            @error('image')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row align-items-center mt-3">
                            <div class="col-lg-2">
                                <label for="remarks">Remarks</label>
                            </div>
                            <div class="col-lg-10">
                                <textarea id="remarks" name="remarks" class="form-control" rows="4"></textarea>
                            </div>
                            @error('remarks')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="modal-footer border-0" style="margin-top:10px">
                            <button type="submit" class="btn btn-primary" id="saveButton" style="margin-right:10px">Add</button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>

           @php
    // group by remarks (remarks could be null)
    $groupedMedia = $mediaItems->groupBy(function($item) {
        return $item->remarks ?? 'No remarks';
    });
@endphp

@forelse($groupedMedia as $remark => $mediaGroup)
    <div class="col-12 mb-3">
        <p><strong>Remarks:</strong> {{ $remark }}</p>

        @php
            // collect admin_remark values for this group (filter out empty/null)
            $adminRemarks = $mediaGroup->pluck('admin_remark')->filter()->unique()->values();
        @endphp

    <p><strong>Admin Remarks & Updated By:</strong></p>
@php
    // collect pairs of admin_remark + updated_by
    $adminDetails = $mediaGroup
        ->filter(function($item) {
            return !empty($item->admin_remark) || !empty($item->updated_by);
        })
        ->map(function($item) {
            return [
                'remark' => $item->admin_remark ?? 'No remark',
                'updated_by' => $item->updated_by ?? 'Unknown',
            ];
        })
        ->unique(function($item) {
            return $item['remark'].'-'.$item['updated_by'];
        })
        ->values();
@endphp

@if($adminDetails->isEmpty())
    <em>No Admin Remarks</em>
@else
    <ul class="mb-0">
        @foreach($adminDetails as $detail)
            <li>
                <strong>{{ ucfirst($detail['updated_by']) }}</strong> — {{ $detail['remark'] }}
            </li>
        @endforeach
    </ul>
@endif


        <div class="row">
            @foreach($mediaGroup as $media)
                <div class="col-md-3 mb-2">
                    @if($media->image_path)
                        <img src="{{ asset('storage/' . $media->image_path) }}" class="img-fluid border" style="height: 150px; object-fit: cover;">
                    @elseif($media->video_path)
                        <video src="{{ asset('storage/' . $media->video_path) }}" controls style="width: 100%; height: 150px; object-fit: cover;"></video>
                    @else
                        <div class="border d-flex align-items-center justify-content-center" style="height:150px;">
                            <small>No media</small>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

       <form action="{{ route('taskmedia.delete', ['id' => $media->id, 'siteId' => $site->id]) }}" 
      method="POST" 
      onsubmit="return confirm('Are you sure you want to delete all media for this remark?');">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm mb-2" style="float:right">Delete Entire Record</button>
</form>
    </div>
@empty
    <div class="col-12">
        <p>No media available.</p>
    </div>
@endforelse


            </div>
        </div>
        
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
 document.getElementById('add-image-field').addEventListener('click', function () {
        let wrapper = document.createElement('div');
        wrapper.classList.add('mb-2', 'input-group');
        wrapper.innerHTML = `
            <input type="file" name="task_images[]" class="form-control" accept="image/*">
            <button type="button" class="btn btn-danger remove-image-field">Remove</button>
        `;
        document.getElementById('image-upload-wrapper').appendChild(wrapper);
    });

    // Add video field
    document.getElementById('add-video-field').addEventListener('click', function () {
        let wrapper = document.createElement('div');
        wrapper.classList.add('mb-2', 'input-group');
        wrapper.innerHTML = `
            <input type="file" name="task_videos[]" class="form-control" accept="video/*">
            <button type="button" class="btn btn-danger remove-video-field">Remove</button>
        `;
        document.getElementById('video-upload-wrapper').appendChild(wrapper);
    });

    // Remove field (image or video)
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-image-field') || e.target.classList.contains('remove-video-field')) {
            e.target.closest('.input-group').remove();
        }
    });
</script>

@endsection
