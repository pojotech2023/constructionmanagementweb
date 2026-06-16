@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header leads-page-header">
                <h3 class="fw-bold mb-3">Check List</h3>
                <ul class="breadcrumbs mb-3">
                    <li class="nav-home">
                        <a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a>
                    </li>
                    <li class="separator"><i class="icon-arrow-right"></i></li>
                    <li class="nav-item"><a href="#">Check List</a></li>
                </ul>
            </div>
            <div class="row">
                <div class="col-12 col-md-8">

                    <!-- Blade alert for success -->
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        {{ session()->forget('success') }} {{-- Clear session --}}
                    @endif

                    <form id="filterform" action="" method="GET" action="{{ route('sitemanagement.list') }}"
                        class="row mb-3">
                     
                        <div class="row mb-3 align-items-center">
                            {{--<div class="col-md-4">
                                <select name="status" id="statusFilter" class="form-select"
                                    onchange="document.getElementById('filterform').submit();">
                                    <option value="" {{ request('status') == '' ? 'selected' : '' }}>All</option>
                                    <option value="New" {{ request('status') == 'New' ? 'selected' : '' }}>New</option>
                                    <option value="Ongoing" {{ request('status') == 'Ongoing' ? 'selected' : '' }}>Ongoing
                                    </option>
                                    <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>
                                        Completed</option>
                                </select>
                            </div>--}}

                           {{--<div class="col-md-8 d-flex justify-content-end">
                                <a href="{{ route('checklist-create') }}" class="btn btn-primary btn-round">
                                    <i class="fa fa-plus"></i> Add checklist
                                </a>
                            </div>--}}
                        </div>

                    </form>
                </div>
            </div>

           
<div class="accordion">
    @foreach($checklists as $checklist)
        @php
            $visibleTasks = session('role_name') == 'Admin'
                ? $checklist->tasks
                : $checklist->tasks;
        @endphp

        @if($visibleTasks->count() > 0)
            <div class="accordion-item">
                <div class="accordion-title">
                    <span style="font-size: 20px; font-weight:bold;">{{ $checklist->stage }}</span>
                    <span class="icon">&#9660;</span>
                </div>

                <div class="accordion-content">
                    <ul style="list-style-type: none; padding-left: 0;">
                        @php $previousApproved = true; @endphp

                        @foreach($visibleTasks as $task)
                            @php
                                // Get latest media record for this task & site
                                $media = $task->media
                                    ->where('site_id', $site->id)
                                    ->sortByDesc('id')
                                    ->first();

                                // Status color
                                if ($media) {
                                    $status = strtolower($media->status ?? '');
                                    $color = match ($status) {
                                        'approved' => 'green',
                                        'rejected' => 'red',
                                        default => 'yellow',
                                    };
                                } else {
                                    $status = 'pending';
                                    $color = 'gray';
                                }

                                // Task approved check
                                $isApproved = $media && strtolower($media->status) === 'approved';
                            @endphp

                            <li style="margin-bottom: 10px; display: flex; align-items: center;">
                                <!-- Status box -->
                                <span style="display:inline-block; width:15px; height:15px; background-color:{{ $color }}; border-radius:3px; margin-right:10px;"></span>

                                @if(session('role_name') == 'Supervisor')
                                    @if($previousApproved)
                                        <a href="{{ route('task.create', ['siteId' => $site->id, 'taskId' => $task->id]) }}"
                                           style="color:black; font-weight:600;">
                                           {{ $task->task_name }}
                                        </a>
                                    @else
                                        <span style="color: grey; cursor: not-allowed;" title="Complete previous task first">
                                            {{ $task->task_name }} 🔒
                                        </span>
                                    @endif

                                    {{-- Set next unlock condition --}}
                                    @php $previousApproved = $isApproved; @endphp
                                @else
                                    {{-- Admin view --}}
                                    <a href="{{ route('admin.taskmedia.view', ['siteId' => $site->id, 'taskId' => $task->id]) }}"
                                       style="color:black; font-weight:600;">
                                       {{ $task->task_name }}
                                    </a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    @endforeach
</div>


<!--model code-->

                  

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
                        @method('PATCH')
                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- End of Delete Confirmation Modal -->

    <!-- Spinner -->
    <div class="d-flex justify-content-center mt-3">
        <div class="spinner-border text-primary d-none" role="status" id="loadingSpinner">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <script>
        //redirect to leads detail page
        function redirectToLeadDetails(event, card) {
            event.stopPropagation(); // Prevents unintended clicks
            // Remove styles from all cards
            document.querySelectorAll('.site-card').forEach(item => {
                item.classList.remove('selected-card');
            });
            // Add active class to clicked card
            card.classList.add('selected-card');
            // Redirect after small delay
            let route = card.getAttribute('data-route');
            if (route) {
                window.location.href = route;
            }
        }

        document.addEventListener("DOMContentLoaded", function() {

            //delete site
            document.querySelectorAll(".deleteButton").forEach(button => {
                button.addEventListener("click", function() {
                    event.stopPropagation();
                    const siteId = this.getAttribute("data-id");
                    const action = "{{ route('sitemanagement.delete', ':id') }}".replace(':id',
                        siteId);
                    document.getElementById("deleteForm").setAttribute("action", action);
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
        });

        
    const items = document.querySelectorAll('.accordion-item');

    items.forEach(item => {
      item.querySelector('.accordion-title').addEventListener('click', () => {
        // Collapse all others (optional)
        items.forEach(i => {
          if (i !== item) i.classList.remove('active');
        });

        item.classList.toggle('active');
      });
    });


    $(document).ready(function(){
    $('.accordion-title').click(function(){
        const content = $(this).next('.accordion-content');
        const icon = $(this).find('.icon');

        if (content.is(':visible')) {
            content.slideUp();
            icon.html('&#9660;'); // Arrow ▼
        } else {
            $('.accordion-content').slideUp(); // Close others
            $('.icon').html('&#9660;'); // Reset all icons
            content.slideDown();
            icon.html('<span class="spinner"></span>'); // Wait icon
        }
    });
});



    </script>

    <style>
        .site-card {
            cursor: pointer;
            transition: all 0.3s ease-in-out;
            border: 2px solid #dee2e6;
            background: #fff;
            border-radius: 8px;
        }

        /* Hover Effect */
        .site-card:hover {
            transform: scale(1.02);
            border-color: #007bff;
            box-shadow: 0px 5px 15px rgba(0, 123, 255, 0.3);
        }

        .accordion {
      max-width: 700px;
      margin: 20px auto;
      background: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .accordion-item {
      border-bottom: 1px solid #ddd;
    }

    .accordion-title {
      padding: 20px;
      cursor: pointer;
      background-color: white;
      font-weight: bold;
      transition: background-color 0.2s ease;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .accordion-title:hover {
      background-color: #f0f0f0;
    }

    .accordion-content {
      display: none;
      padding: 0 20px 20px 20px;
      background-color: white;
      font-size: 15px;
      line-height: 1.5;
    }

    .accordion-item.active .accordion-content {
      display: block;
    }

    .accordion-item.active .accordion-title {
      color: #3e64ff;
    }

    .arrow {
      font-size: 16px;
      transition: transform 0.2s ease;
    }

    .accordion-item.active .arrow {
      transform: rotate(180deg); /* Switches arrow direction */
    }
    

.accordion-content {
    display: none;
    padding: 10px;
    background: #fff;
}

.spinner {
    border: 2px solid #ccc;
    border-top: 2px solid #3498db;
    border-radius: 50%;
    width: 14px;
    height: 14px;
    animation: spin 1s linear infinite;
    display: inline-block;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
a {
    color: #1f2328;
    text-decoration: none !important;
}
    </style>
@endsection
