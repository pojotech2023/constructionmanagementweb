@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">Admin Control</h3>
                <ul class="breadcrumbs mb-3">
                    <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
                    <li class="separator"><i class="icon-arrow-right"></i></li>
                    <li class="nav-item"><a href="#">Admin Control</a></li>
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
                        <div class="card-header">
                            <h4 class="card-title mb-0">
                                <i class="fa fa-toggle-on me-2 text-success"></i>
                                Sidebar &amp; Feature Visibility
                            </h4>
                            <small class="text-muted">Untick an item to hide it from the sidebar / pages for all users.</small>
                        </div>

                        <div class="card-body">
                            <form action="{{ route('admin.control.update') }}" method="POST">
                                @csrf

                                @foreach ($menuGroups as $groupKey => $group)
                                    <div class="border rounded px-3 py-2 mb-3" style="background:#f8f9fa;">
                                        <div class="d-flex align-items-center gap-2">
                                            <input class="form-check-input m-0"
                                                type="checkbox"
                                                id="menu_{{ $groupKey }}"
                                                name="menu[{{ $groupKey }}]"
                                                value="1"
                                                {{ $visibility[$groupKey] ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold mb-0" for="menu_{{ $groupKey }}">
                                                {{ $group['label'] }}
                                            </label>

                                            @if (!empty($group['children']))
                                                <i class="fa fa-chevron-up ms-auto"></i>
                                            @endif
                                        </div>

                                        @if (!empty($group['children']))
                                            <div class="row row-cols-2 row-cols-sm-3 row-cols-lg-4 g-2 mt-1 ms-4">
                                                @foreach ($group['children'] as $child)
                                                    <div class="col">
                                                        <div class="border rounded px-3 py-2 d-flex align-items-center gap-2 h-100" style="background:#eef6ff;">
                                                            <input class="form-check-input m-0"
                                                                type="checkbox"
                                                                id="menu_{{ $child['key'] }}"
                                                                name="menu[{{ $child['key'] }}]"
                                                                value="1"
                                                                {{ $visibility[$child['key']] ? 'checked' : '' }}>
                                                            <label class="form-check-label mb-0 small" for="menu_{{ $child['key'] }}">
                                                                {{ $child['label'] }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="fa fa-save me-1"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
