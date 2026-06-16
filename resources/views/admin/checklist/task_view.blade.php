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
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">Checklist</a></li>
                <li class="separator"><i class="icon-arrow-right"></i></li>
                <li class="nav-item"><a href="#">Task Update Form</a></li>
            </ul>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4 class="card-title mb-0">Task Update Form</h4>
                    </div>

                    <div class="card-body">

                        {{-- Success Alert --}}
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            {{ session()->forget('success') }}
                        @endif

                        @if($taskMedia->isNotEmpty())
                            @php
                                $groupedMedia = $taskMedia->groupBy(function($item) {
                                    return $item->remarks ?? 'No Remark';
                                });
                            @endphp

                            @foreach($groupedMedia as $remark => $mediaGroup)
                                <div class="mb-4 border rounded p-3 bg-light">
                                    <p><strong>Supervisor Remark:</strong> {{ $remark }}</p>
                                    @php
                                        $adminRemark = $mediaGroup->pluck('admin_remark')->filter()->unique()->implode(', ');
                                    @endphp
                                    <p><strong>Admin:</strong> {{ $adminRemark ?: 'Wait for admin response' }}</p>

                                    <div class="media-grid">
                                        @foreach($mediaGroup as $media)
                                            <div class="media-item">
                                                @if($media->image_path)
                                                    <img src="{{ asset('storage/' . $media->image_path) }}" class="img-fluid border rounded" alt="Task Image">
                                                @elseif($media->video_path)
                                                    <video controls style="width:100%; height:150px; object-fit:cover;">
                                                        <source src="{{ asset('storage/' . $media->video_path) }}" type="video/mp4">
                                                        Your browser does not support the video tag.
                                                    </video>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach

                            {{-- Admin Update Form --}}
                            <form action="{{ route('admin.taskmedia.update', $media->id) }}" method="POST" id="adminForm">
                                @csrf
                                @method('PUT')

                                <div class="form-group mt-3">
                                    <label>Status</label>
                                    <select name="status" id="status" class="form-control mt-1" required>
                                        <option value="">-- Select Status --</option>
                                        <option value="approved" {{ $media->status == 'approved' ? 'selected' : '' }}>Approve</option>
                                        <option value="rejected" {{ $media->status == 'rejected' ? 'selected' : '' }}>Reject</option>
                                    </select>
                                </div>

                                <div class="form-group mt-3" id="remarkField" style="display: none;">
                                    <label>Remark (required if Rejected)</label>
                                    <textarea name="admin_remark" id="admin_remark" class="form-control" rows="3">{{ $media->admin_remark }}</textarea>
                                </div>

                                <button class="btn btn-primary mt-3 float-end">Submit</button>
                            </form>
                        @else
                            <p>No task media available.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Spinner --}}
<div class="d-flex justify-content-center mt-3">
    <div class="spinner-border text-primary d-none" role="status" id="loadingSpinner">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

{{-- JS Section --}}
<script>
document.addEventListener("DOMContentLoaded", function() {
    const statusSelect = document.getElementById('status');
    const remarkField = document.getElementById('remarkField');
    const remarkInput = document.getElementById('admin_remark');
    const form = document.getElementById('adminForm');
    const spinner = document.getElementById('loadingSpinner');

    function toggleRemark() {
        if (statusSelect.value === 'rejected') {
            remarkField.style.display = 'block';
            remarkInput.setAttribute('required', 'required');
        } else {
            remarkField.style.display = 'none';
            remarkInput.removeAttribute('required');
            remarkInput.value = '';
        }
    }

    toggleRemark();
    statusSelect.addEventListener('change', toggleRemark);

    form.addEventListener('submit', function(e) {
        if (statusSelect.value === 'rejected' && !remarkInput.value.trim()) {
            e.preventDefault();
            alert('Please enter a remark when rejecting.');
            return;
        }
        spinner.classList.remove('d-none'); // show spinner
    });
});
</script>

<style>
.media-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-top: 10px;
}
.media-item {
    width: 220px;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 8px;
    background: #fff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.media-item img,
.media-item video {
    width: 100%;
    border-radius: 6px;
}
</style>
@endsection
