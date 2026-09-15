@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-inner">
            <div class="page-header mb-1">
                <div>
                    <h3 class="fw-bold mb-1">Settings</h3>
                    <p class="text-muted mb-3">Manage units and terms &amp; conditions templates used across the app.</p>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <ul class="nav settings-tabs mb-4" id="settingsTabBar" role="tablist">
                @if ($isAdmin)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link settings-tab-btn" id="tab-unit-btn" data-tab-target="#tab-unit"
                            data-tab-key="unit" type="button" role="tab" aria-controls="tab-unit">
                            <i class="bi bi-rulers"></i>
                            <span>Unit Master</span>
                        </button>
                    </li>
                @endif
                <li class="nav-item" role="presentation">
                    <button class="nav-link settings-tab-btn" id="tab-terms-btn" data-tab-target="#tab-terms"
                        data-tab-key="terms" type="button" role="tab" aria-controls="tab-terms">
                        <i class="fa-solid fa-file-contract"></i>
                        <span>Terms &amp; Conditions</span>
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="settingsTabContent">

                @if ($isAdmin)
                    {{-- ===================== Unit Master tab ===================== --}}
                    <div class="settings-tab-pane" id="tab-unit" role="tabpanel" aria-labelledby="tab-unit-btn">
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h4 class="card-title mb-0">Unit List</h4>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUnitModal">
                                        <i class="fa fa-plus"></i> Add Unit
                                    </button>
                                </div>
                            </div>

                            <div class="card-body">
                                @if ($units->isEmpty())
                                    <p class="text-center mt-3">No units found. Please add a unit.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="display table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>S.No</th>
                                                    <th>Unit Name</th>
                                                    <th style="width: 10%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($units as $unit)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $unit->name }}</td>
                                                        <td>
                                                            <div class="form-button-action">
                                                                <button type="button" class="btn btn-link btn-primary btn-lg unitEditButton"
                                                                    data-id="{{ $unit->id }}" data-name="{{ $unit->name }}"
                                                                    data-bs-toggle="modal" data-bs-target="#editUnitModal">
                                                                    <i class="fa fa-edit"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-link btn-danger unitDeleteButton"
                                                                    data-id="{{ $unit->id }}" data-bs-toggle="modal"
                                                                    data-bs-target="#deleteUnitModal">
                                                                    <i class="fa fa-times"></i>
                                                                </button>
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

                    <!-- Add Unit Modal -->
                    <div class="modal fade" id="addUnitModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <form action="{{ route('unit.add') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="tab" value="unit">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Add Unit</h5>
                                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="name">Unit Name</label>
                                            <input type="text" name="name" id="name" class="form-control" placeholder="e.g. Kg, Bag, Ton" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Unit Modal -->
                    <div class="modal fade" id="editUnitModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <form id="editUnitForm" action="" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="tab" value="unit">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Unit</h5>
                                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="edit_name">Unit Name</label>
                                            <input type="text" name="name" id="edit_name" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Unit Confirmation Modal -->
                    <div class="modal fade" id="deleteUnitModal" tabindex="-1" role="dialog" aria-labelledby="deleteUnitModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deleteUnitModalLabel">Confirm Delete</h5>
                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    Are you sure you want to delete this unit?
                                </div>
                                <div class="modal-footer">
                                    <form id="deleteUnitForm" action="" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="tab" value="unit">
                                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ===================== Terms & Conditions tab ===================== --}}
                <div class="settings-tab-pane" id="tab-terms" role="tabpanel" aria-labelledby="tab-terms-btn">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h4 class="card-title mb-0">Terms &amp; Conditions Templates</h4>
                                    <p class="text-muted mb-0 mt-1">
                                        Add a title and its description here. In Quotation, Sales Bill and Purchase Bill,
                                        typing a matching title auto-fills the description as the Terms &amp; Conditions on the PDF.
                                    </p>
                                </div>
                                <button type="button" class="btn btn-primary text-nowrap" data-bs-toggle="modal" data-bs-target="#addTermsModal">
                                    <i class="fa fa-plus"></i> Add Template
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            @if ($termsConditions->isEmpty())
                                <p class="text-center mt-3">No terms &amp; conditions templates found. Please add one.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="display table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Title</th>
                                                <th>Description</th>
                                                <th style="width: 10%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($termsConditions as $termsCondition)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $termsCondition->title }}</td>
                                                    <td>{{ \Illuminate\Support\Str::limit($termsCondition->description, 120) }}</td>
                                                    <td>
                                                        <div class="form-button-action">
                                                            <button type="button" class="btn btn-link btn-primary btn-lg termsEditButton"
                                                                data-id="{{ $termsCondition->id }}"
                                                                data-title="{{ $termsCondition->title }}"
                                                                data-description="{{ $termsCondition->description }}"
                                                                data-bs-toggle="modal" data-bs-target="#editTermsModal">
                                                                <i class="fa fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-link btn-danger termsDeleteButton"
                                                                data-id="{{ $termsCondition->id }}" data-bs-toggle="modal"
                                                                data-bs-target="#deleteTermsModal">
                                                                <i class="fa fa-times"></i>
                                                            </button>
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

                <!-- Add Terms & Conditions Modal -->
                <div class="modal fade" id="addTermsModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <form action="{{ route('terms-condition.add') }}" method="POST">
                                @csrf
                                <input type="hidden" name="tab" value="terms">
                                <div class="modal-header">
                                    <h5 class="modal-title">Add Terms &amp; Conditions Template</h5>
                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="title">Title</label>
                                        <input type="text" name="title" id="title" class="form-control"
                                            placeholder="e.g. Standard Payment Terms" required>
                                    </div>
                                    <div class="form-group mt-3">
                                        <label for="description">Description</label>
                                        <textarea name="description" id="description" class="form-control" rows="5"
                                            placeholder="This text will appear as Terms & Conditions in the PDF" required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Save</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Edit Terms & Conditions Modal -->
                <div class="modal fade" id="editTermsModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <form id="editTermsForm" action="" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="tab" value="terms">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Terms &amp; Conditions Template</h5>
                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="edit_title">Title</label>
                                        <input type="text" name="title" id="edit_title" class="form-control" required>
                                    </div>
                                    <div class="form-group mt-3">
                                        <label for="edit_description">Description</label>
                                        <textarea name="description" id="edit_description" class="form-control" rows="5" required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Update</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Delete Terms & Conditions Confirmation Modal -->
                <div class="modal fade" id="deleteTermsModal" tabindex="-1" role="dialog" aria-labelledby="deleteTermsModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteTermsModalLabel">Confirm Delete</h5>
                                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                Are you sure you want to delete this template?
                            </div>
                            <div class="modal-footer">
                                <form id="deleteTermsForm" action="" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="tab" value="terms">
                                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ---------- Tab switching ----------
            const tabButtons = document.querySelectorAll('.settings-tab-btn');
            const tabPanes = document.querySelectorAll('.settings-tab-pane');

            function activateTab(key) {
                let matched = false;
                tabButtons.forEach(function(btn) {
                    const isMatch = btn.getAttribute('data-tab-key') === key;
                    btn.classList.toggle('active', isMatch);
                    if (isMatch) matched = true;
                });
                tabPanes.forEach(function(pane) {
                    pane.classList.toggle('active', ('#' + pane.id) === (document.querySelector('.settings-tab-btn[data-tab-key="' + key + '"]')?.getAttribute('data-tab-target')));
                });
                if (!matched && tabButtons.length) {
                    // Fallback to first available tab
                    const firstKey = tabButtons[0].getAttribute('data-tab-key');
                    tabButtons.forEach(btn => btn.classList.toggle('active', btn === tabButtons[0]));
                    tabPanes.forEach(function(pane) {
                        pane.classList.toggle('active', ('#' + pane.id) === tabButtons[0].getAttribute('data-tab-target'));
                    });
                }
            }

            tabButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const key = btn.getAttribute('data-tab-key');
                    activateTab(key);
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', key);
                    window.history.replaceState({}, '', url);
                });
            });

            const params = new URLSearchParams(window.location.search);
            const requestedTab = params.get('tab') || (tabButtons[0] ? tabButtons[0].getAttribute('data-tab-key') : null);
            if (requestedTab) {
                activateTab(requestedTab);
            }

            // ---------- Unit Master modals ----------
            $('.unitEditButton').click(function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                var action = "{{ route('unit.update', ':id') }}".replace(':id', id);
                $('#editUnitForm').attr('action', action);
                $('#edit_name').val(name);
            });

            $('.unitDeleteButton').click(function() {
                var id = $(this).data('id');
                var action = "{{ route('unit.delete', ':id') }}".replace(':id', id);
                $('#deleteUnitForm').attr('action', action);
            });

            // ---------- Terms & Conditions modals ----------
            $('.termsEditButton').click(function() {
                var id = $(this).data('id');
                var title = $(this).data('title');
                var description = $(this).data('description');
                var action = "{{ route('terms-condition.update', ':id') }}".replace(':id', id);
                $('#editTermsForm').attr('action', action);
                $('#edit_title').val(title);
                $('#edit_description').val(description);
            });

            $('.termsDeleteButton').click(function() {
                var id = $(this).data('id');
                var action = "{{ route('terms-condition.delete', ':id') }}".replace(':id', id);
                $('#deleteTermsForm').attr('action', action);
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

    <style>
        .settings-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            border-bottom: 1px solid #e5e7eb;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .settings-tab-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: none;
            border: none;
            border-bottom: 2px solid transparent;
            padding: 10px 6px;
            margin-right: 20px;
            color: #64748b;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: color 0.15s ease, border-color 0.15s ease;
        }

        .settings-tab-btn i {
            font-size: 14px;
        }

        .settings-tab-btn:hover {
            color: #334155;
        }

        .settings-tab-btn.active {
            color: #0d6efd;
            border-bottom-color: #0d6efd;
        }

        .settings-tab-pane {
            display: none;
        }

        .settings-tab-pane.active {
            display: block;
        }
    </style>
@endsection
