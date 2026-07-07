@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Supervisor Permissions</h3>
                <ul class="breadcrumbs mb-3">
                    <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
                    <li class="separator"><i class="icon-arrow-right"></i></li>
                    <li class="nav-item"><a href="{{ route('supervisor.list') }}">Supervisor</a></li>
                    <li class="separator"><i class="icon-arrow-right"></i></li>
                    <li class="nav-item"><a href="#">Permissions</a></li>
                </ul>
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
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div>
                                <h4 class="card-title mb-0">
                                    <i class="fa fa-key me-2 text-success"></i>
                                    Permissions &mdash;
                                    <span class="text-primary">{{ $supervisor->name }}</span>
                                </h4>
                                <small class="text-muted">{{ $supervisor->email }}</small>
                            </div>
                            <a href="{{ route('supervisor.list') }}" class="btn btn-secondary btn-sm">
                                <i class="fa fa-arrow-left me-1"></i> Back
                            </a>
                        </div>

                        <div class="card-body">
                            <form action="{{ route('supervisor.permissions.save', $supervisor->id) }}" method="POST">
                                @csrf

                                {{-- Assigned Sites --}}
                                <div class="mb-5">
                                    <div class="d-flex align-items-center mb-3">
                                        <span class="badge bg-dark px-3 py-2 me-3" style="font-size:13px; white-space:nowrap;">
                                            Assigned Sites
                                        </span>
                                        <hr class="flex-grow-1 m-0">
                                    </div>

                                    <small class="text-muted d-block mb-2">
                                        Select the sites this supervisor can view. A site can be assigned to only one supervisor at a time &mdash; checking a site here will move it away from any other supervisor.
                                    </small>

                                    <div class="row row-cols-2 row-cols-sm-3 row-cols-lg-4 g-2 ms-1">
                                        @forelse ($sites as $site)
                                            @php
                                                $siteChecked = in_array($site->id, $assignedSiteIds);
                                            @endphp
                                            <div class="col">
                                                <div class="border rounded px-3 py-2 d-flex align-items-center gap-2 h-100"
                                                    style="background:#f8f9fa;">
                                                    <input class="form-check-input m-0"
                                                        type="checkbox"
                                                        id="site_{{ $site->id }}"
                                                        name="sites[]"
                                                        value="{{ $site->id }}"
                                                        {{ $siteChecked ? 'checked' : '' }}>
                                                    <label class="form-check-label mb-0 small" for="site_{{ $site->id }}">
                                                        {{ $site->site_name }}
                                                    </label>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-muted ms-1">No sites available.</p>
                                        @endforelse
                                    </div>
                                </div>

                                @foreach ($modules as $moduleKey => $module)
                                    <div class="mb-5">

                                        {{-- Module Header --}}
                                        <div class="d-flex align-items-center mb-3">
                                            <span class="badge bg-dark px-3 py-2 me-3" style="font-size:13px; white-space:nowrap;">
                                                {{ $module['label'] }}
                                            </span>
                                            <hr class="flex-grow-1 m-0">
                                        </div>

                                        @php
                                            $topActions = array_filter($module['actions'], fn($a) => is_null($a['parent']));
                                        @endphp

                                        {{-- Top-level permissions: horizontal row of pill-style checkboxes --}}
                                        <div class="d-flex flex-wrap gap-3 mb-3 ms-1">
                                            @foreach ($topActions as $actionKey => $action)
                                                @php
                                                    $isChecked  = isset($saved[$moduleKey . '.' . $actionKey]) && $saved[$moduleKey . '.' . $actionKey]->is_allowed;
                                                    $hasChildren = !empty(array_filter($module['actions'], fn($a) => $a['parent'] === $actionKey));
                                                @endphp
                                                <div class="border rounded px-3 py-2 d-flex align-items-center gap-2"
                                                    style="background:#f8f9fa; min-width:130px;">
                                                    <input class="form-check-input m-0 parent-checkbox"
                                                        type="checkbox"
                                                        id="perm_{{ $moduleKey }}_{{ $actionKey }}"
                                                        name="permissions[{{ $moduleKey }}][{{ $actionKey }}]"
                                                        value="1"
                                                        data-target="sub_{{ $moduleKey }}_{{ $actionKey }}"
                                                        {{ $isChecked ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-semibold mb-0"
                                                        for="perm_{{ $moduleKey }}_{{ $actionKey }}">
                                                        {{ $action['label'] }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>

                                        {{-- Sub-permission groups: each parent's children shown in a 3-4 col horizontal grid --}}
                                        @foreach ($topActions as $actionKey => $action)
                                            @php
                                                $subActions = array_filter($module['actions'], fn($a) => $a['parent'] === $actionKey);
                                                $isParentChecked = isset($saved[$moduleKey . '.' . $actionKey]) && $saved[$moduleKey . '.' . $actionKey]->is_allowed;
                                            @endphp
                                            @if (!empty($subActions))
                                                <div id="sub_{{ $moduleKey }}_{{ $actionKey }}"
                                                    class="ms-3 mb-3"
                                                    style="{{ $isParentChecked ? '' : 'display:none;' }}">

                                                    <div class="d-flex align-items-center mb-2">
                                                        <small class="text-muted fw-semibold me-2">{{ $action['label'] }} &rarr;</small>
                                                        <hr class="flex-grow-1 m-0" style="border-style:dashed;">
                                                    </div>

                                                    <div class="row row-cols-2 row-cols-sm-3 row-cols-lg-4 g-2 ms-1">
                                                        @foreach ($subActions as $subKey => $subAction)
                                                            @php
                                                                $subChecked = isset($saved[$moduleKey . '.' . $subKey]) && $saved[$moduleKey . '.' . $subKey]->is_allowed;
                                                            @endphp
                                                            <div class="col">
                                                                <div class="border rounded px-3 py-2 d-flex align-items-center gap-2 h-100"
                                                                    style="background:#eef6ff;">
                                                                    <input class="form-check-input m-0"
                                                                        type="checkbox"
                                                                        id="perm_{{ $moduleKey }}_{{ $subKey }}"
                                                                        name="permissions[{{ $moduleKey }}][{{ $subKey }}]"
                                                                        value="1"
                                                                        {{ $subChecked ? 'checked' : '' }}>
                                                                    <label class="form-check-label mb-0 small"
                                                                        for="perm_{{ $moduleKey }}_{{ $subKey }}">
                                                                        {{ $subAction['label'] }}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach

                                    </div>
                                @endforeach

                                <div class="mt-2">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="fa fa-save me-1"></i> Save Permissions
                                    </button>
                                    <button type="button" class="btn btn-warning ms-2" id="resetPermissions">
                                        <i class="fa fa-refresh me-1"></i> Reset
                                    </button>
                                    <a href="{{ route('supervisor.list') }}" class="btn btn-danger ms-2">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.parent-checkbox').forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    const targetId = this.getAttribute('data-target');
                    if (!targetId) return;
                    const subGroup = document.getElementById(targetId);
                    if (subGroup) {
                        subGroup.style.display = this.checked ? '' : 'none';
                        if (!this.checked) {
                            subGroup.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
                        }
                    }
                });
            });

            document.getElementById('resetPermissions').addEventListener('click', function () {
                if (!confirm('Reset all permissions for this supervisor?')) return;

                // Uncheck every checkbox
                document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);

                // Hide all sub-groups
                document.querySelectorAll('.sub-group, [id^="sub_"]').forEach(el => el.style.display = 'none');
            });
        });
    </script>
@endsection
