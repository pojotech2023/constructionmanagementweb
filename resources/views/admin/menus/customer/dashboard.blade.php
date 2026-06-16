@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="mb-4 fw-bold text-primary">📋 Project Checklist</h2>

    @foreach ($checklists as $checklist)
        @php
            $hasMedia = false;
            foreach ($checklist['tasks'] as $task) {
                if (count($task['media']) > 0) {
                    $hasMedia = true;
                    break;
                }
            }
        @endphp

        @if ($hasMedia)
        <div class="mb-5">
            <h4 class="mb-3">
                <span class="badge bg-info text-dark px-3 py-2 fs-6">{{ $checklist['stage'] }}</span>
            </h4>

            @foreach ($checklist['tasks'] as $task)
               
                @if (count($task['media']) > 0)
                
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h5 class="mb-0 text-primary">{{ $task['task_name'] }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach ($task['media'] as $media)
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100 border rounded shadow-sm">
                                        <div class="card-body d-flex flex-column">
                                            <!-- Remarks -->
                                            @if (!empty($media['remarks']))
                                                <p class="fw-bold text-muted mb-1">Remarks:</p>
                                                <p class="text-dark mb-2">{{ $media['remarks'] }}</p>
                                            @endif

                                            <!-- Image -->
                                            @if (!empty($media['image_path']))
                                                <img src="{{ asset('storage/' . $media['image_path']) }}" 
                                                    alt="Image" class="img-fluid mb-2 rounded border shadow-sm"
                                                    style="max-height: 200px; object-fit: cover;">
                                            @endif

                                            <!-- Video -->
                                            @if (!empty($media['video_path']))
                                                <video controls class="w-100 rounded border" style="max-height: 200px; object-fit: cover;">
                                                    <source src="{{ asset('storage/' . $media['video_path']) }}" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
        @endif
    @endforeach
</div>

<style>
    .card {
        border-radius: 12px;
        transition: transform 0.2s ease;
    }

    .card:hover {
        transform: scale(1.01);
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
</style>
@endsection
