@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                    <h3 class="fw-bold mb-3 me-3">Material Requests</h3>
                    <ul class="breadcrumbs mb-0">
                        <li class="nav-home">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="icon-home"></i>
                            </a>
                        </li>
                        <li class="separator"><i class="icon-arrow-right"></i></li>
                        <li class="nav-item">
                            <a href="{{ route('sitemanagement.list') }}">Site</a>
                        </li>
                        <li class="separator"><i class="icon-arrow-right"></i></li>
                        <li class="nav-item">
                            <a href="{{ route('site.detail', $site->id) }}">{{ $site->site_name }}</a>
                        </li>
                        <li class="separator"><i class="icon-arrow-right"></i></li>
                        <li class="nav-item"><a href="#">View Request</a></li>
                    </ul>
                </div>
                <a href="{{ route('material.detail', $site->id) }}" class="btn btn-secondary btn-sm mb-3">
                    <i class="fa fa-arrow-left me-1"></i> Back
                </a>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">All Requests &mdash; {{ $site->site_name }}</h4>
                        </div>

                        <div class="card-body">
                            @if ($requests->isEmpty())
                                <p class="text-center mt-3">No material requests found for this site.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="display table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Material / Item</th>
                                                <th>Quantity</th>
                                                <th>Unit</th>
                                                <th>Needed By</th>
                                                <th>Vendor</th>
                                                <th>Remarks</th>
                                                <th>Status</th>
                                                <th style="width: 14%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($requests as $req)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $req->items ?? $req->material_type }}</td>
                                                    <td>{{ $req->quantity }}</td>
                                                    <td>{{ $req->unit }}</td>
                                                    <td>{{ $req->date_of_delivery ?? $req->delivery_needed_by }}</td>
                                                    <td>{{ $req->vendor->name ?? '-' }}</td>
                                                    <td>
                                                        {{ $req->remarks }}
                                                        @if ($req->status == \App\Models\MaterialRequest::STATUS_REJECTED && $req->admin_remark)
                                                            <br><small class="text-danger">Rejection reason: {{ $req->admin_remark }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($req->status == \App\Models\MaterialRequest::STATUS_APPROVED)
                                                            <span class="badge badge-success">Approved</span>
                                                        @elseif ($req->status == \App\Models\MaterialRequest::STATUS_REJECTED)
                                                            <span class="badge badge-danger">Rejected</span>
                                                        @else
                                                            <span class="badge badge-warning">Pending</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($req->status == \App\Models\MaterialRequest::STATUS_PENDING)
                                                            <div class="d-flex gap-1">
                                                                <form action="{{ route('material.request.updateStatus', $req->id) }}" method="POST">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <input type="hidden" name="status" value="approved">
                                                                    <button type="submit" class="btn btn-sm btn-success">
                                                                        <i class="fa fa-check"></i> Approve
                                                                    </button>
                                                                </form>
                                                                <button type="button" class="btn btn-sm btn-danger rejectButton"
                                                                    data-id="{{ $req->id }}" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                                                    <i class="fa fa-times"></i> Reject
                                                                </button>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">
                                                                Reviewed {{ $req->reviewed_at ? $req->reviewed_at->diffForHumans() : '' }}
                                                            </span>
                                                        @endif
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
            </div>
        </div>
    </div>

    <!-- Reject Reason Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="rejectForm" action="" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="rejected">
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="admin_remark">Reason (optional)</label>
                            <textarea name="admin_remark" id="admin_remark" class="form-control" rows="3"
                                placeholder="Let the supervisor know why this was rejected"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Reject Request</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.rejectButton').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const action = "{{ route('material.request.updateStatus', ':id') }}".replace(':id', id);
                    document.getElementById('rejectForm').setAttribute('action', action);
                });
            });

            const successAlert = document.querySelector(".alert-success");
            if (successAlert) {
                setTimeout(() => {
                    successAlert.classList.remove("show");
                    successAlert.classList.add("fade");
                }, 3000);
            }
        });
    </script>
@endsection
