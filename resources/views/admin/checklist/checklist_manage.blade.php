@extends('layouts.app')
@section('content')
    <style>
        .page-header {
            padding-bottom: 14px;
            border-bottom: 1px solid #e7edf6;
            margin-bottom: 20px;
        }

        .manage-hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 24px;
            padding: 28px 26px;
            border: 1px solid #dcf0ea;
            border-radius: 14px;
            background: linear-gradient(110deg, #eef4ff 0%, #edf9f3 100%);
            box-shadow: 0 10px 26px rgba(20, 35, 70, 0.06);
        }

        .hero-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
            color: #087443;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .hero-kicker::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #18b564;
        }

        .manage-hero h2 {
            margin: 0;
            color: #07164a;
            font-size: 24px;
            font-weight: 900;
        }

        .manage-hero p {
            max-width: 680px;
            margin: 8px 0 0;
            color: #58667a;
            font-size: 15px;
            font-weight: 600;
            line-height: 1.6;
        }

        .manage-hero p i {
            color: #246bfe;
            margin: 0 2px;
        }

        .hero-action {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-width: 160px;
            padding: 14px 20px;
            border-radius: 10px;
            color: #fff;
            background: linear-gradient(135deg, #246bfe 0%, #11a868 100%);
            font-weight: 800;
            box-shadow: 0 10px 22px rgba(36, 107, 254, 0.24);
            flex: 0 0 auto;
            text-decoration: none;
            cursor: pointer;
        }

        .hero-action:hover {
            color: #fff;
            text-decoration: none;
            opacity: 0.92;
        }

        .checklist-board {
            display: grid;
            gap: 20px;
        }

        .manage-stage-card {
            overflow: hidden;
            border: 1px solid #e8edf6;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 10px 26px rgba(20, 35, 70, 0.07);
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }

        .manage-stage-card.stage-ghost {
            opacity: 0.4;
        }

        .manage-stage-body {
            max-height: 2000px;
            padding: 6px 18px 18px;
            border-top: 1px solid #edf1f7;
            transition: max-height 0.25s ease, padding 0.25s ease;
        }

        .manage-stage-card.collapsed .manage-stage-body {
            max-height: 0;
            padding-top: 0;
            padding-bottom: 0;
            overflow: hidden;
            border-top: 0;
        }

        .stage-toggle-btn {
            width: 38px;
            height: 38px;
            border: none;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #246bfe;
            background: #eaf1ff;
            transition: transform 0.2s ease;
        }

        .manage-stage-card.collapsed .stage-toggle-btn {
            transform: rotate(-90deg);
        }

        .manage-stage-header {
            width: 100%;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            background: #fff;
        }

        .stage-title-wrap {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            cursor: grab;
        }

        .drag-handle-icon {
            color: #b7c1d1;
            font-size: 16px;
        }

        .stage-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: linear-gradient(135deg, #246bfe, #18b564);
            box-shadow: 0 10px 20px rgba(36, 107, 254, 0.22);
            flex-shrink: 0;
        }

        .stage-title-wrap h4 {
            margin: 0;
            color: #07164a;
            font-size: 18px;
            font-weight: 800;
        }

        .stage-title-wrap p {
            margin: 4px 0 0;
            color: #6b778c;
            font-weight: 600;
            font-size: 13px;
        }

        .stage-delete-btn {
            width: 38px;
            height: 38px;
            border: none;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #c82435;
            background: #ffe8eb;
            flex-shrink: 0;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .stage-delete-btn:hover {
            background: #f05260;
            color: #fff;
        }

        .task-list {
            list-style: none;
            margin: 12px 0 0;
            padding: 0;
            display: grid;
            gap: 10px;
        }

        .task-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border: 1px solid #e9eef6;
            border-radius: 8px;
            background: #fbfcfe;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .task-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(20, 35, 70, 0.08);
            background: #fff;
        }

        .task-item.task-ghost {
            opacity: 0.4;
        }

        .task-drag-handle {
            color: #b7c1d1;
            cursor: grab;
            font-size: 13px;
        }

        .task-name {
            flex: 1;
            color: #0d1b4c;
            font-weight: 700;
            font-size: 14px;
        }

        .task-delete-btn {
            width: 30px;
            height: 30px;
            border: none;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #c82435;
            background: #ffe8eb;
            flex-shrink: 0;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .task-delete-btn:hover {
            background: #f05260;
            color: #fff;
        }

        .empty-checklist {
            padding: 42px 24px;
            border: 1px dashed #b9c5d6;
            border-radius: 16px;
            text-align: center;
            background: #fff;
        }

        .empty-checklist i {
            color: #246bfe;
            font-size: 32px;
            margin-bottom: 12px;
        }

        .empty-checklist h4 {
            color: #07164a;
            font-weight: 800;
        }

        .empty-checklist p {
            color: #6b778c;
            margin: 0;
        }

        @media (max-width: 767px) {
            .manage-hero {
                align-items: flex-start;
                flex-direction: column;
            }

            .hero-action {
                width: 100%;
            }
        }
    </style>

    <div class="container">
        <div class="page-inner">
            <div class="page-header leads-page-header">
                <h3 class="fw-bold mb-3">Manage Checklist</h3>
                <ul class="breadcrumbs mb-3">
                    <li class="nav-home">
                        <a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a>
                    </li>
                    <li class="separator"><i class="icon-arrow-right"></i></li>
                    <li class="nav-item"><a href="#">Manage Checklist</a></li>
                </ul>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="manage-hero">
                <div>
                    <span class="hero-kicker">Checklist Templates</span>
                    <h2>Stages &amp; Items</h2>
                    <p>Drag <i class="fa fa-arrows-alt"></i> to reorder stages and checklist items. Deletions apply everywhere this checklist is used.</p>
                </div>
                <a href="{{ route('checklist-create') }}" class="hero-action">
                    <i class="fa fa-plus"></i>
                    <span>Add Checklist</span>
                </a>
            </div>

            @if ($checklists->isEmpty())
                <div class="empty-checklist">
                    <i class="fa fa-clipboard"></i>
                    <h4>No checklist found</h4>
                    <p>Add checklist stages to start tracking site work.</p>
                </div>
            @else
                <div id="stageList" class="checklist-board">
                    @foreach ($checklists as $checklist)
                        <div class="manage-stage-card {{ $loop->first ? '' : 'collapsed' }}" data-id="{{ $checklist->id }}">
                            <div class="manage-stage-header">
                                <div class="stage-title-wrap stage-drag-handle">
                                    <span class="drag-handle-icon"><i class="fa fa-grip-vertical"></i></span>
                                    <span class="stage-icon"><i class="fa fa-tasks"></i></span>
                                    <div>
                                        <h4>{{ $checklist->stage }}</h4>
                                        <p>{{ $checklist->tasks->count() }} {{ Str::plural('item', $checklist->tasks->count()) }}</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="stage-delete-btn deleteStageBtn"
                                        data-id="{{ $checklist->id }}" data-bs-toggle="modal" data-bs-target="#deleteStageModal"
                                        title="Delete stage">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                    <button type="button" class="stage-toggle-btn" title="Expand / collapse">
                                        <i class="fa fa-chevron-down"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="manage-stage-body">
                                @if ($checklist->tasks->isEmpty())
                                    <p class="text-muted mb-0 px-2">No checklist items in this stage.</p>
                                @else
                                    <ul class="task-list" data-checklist-id="{{ $checklist->id }}">
                                        @foreach ($checklist->tasks as $task)
                                            <li class="task-item" data-id="{{ $task->id }}">
                                                <span class="task-drag-handle"><i class="fa fa-grip-vertical"></i></span>
                                                <span class="task-name">{{ $task->task_name }}</span>
                                                <button type="button" class="task-delete-btn deleteTaskBtn"
                                                    data-id="{{ $task->id }}" data-bs-toggle="modal" data-bs-target="#deleteTaskModal"
                                                    title="Delete item">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Delete Task Confirm Modal -->
    <div class="modal fade" id="deleteTaskModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this checklist item?
                </div>
                <div class="modal-footer">
                    <form id="deleteTaskForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Stage Confirm Modal -->
    <div class="modal fade" id="deleteStageModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this entire stage? All checklist items under it will be removed too.
                </div>
                <div class="modal-footer">
                    <form id="deleteStageForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('admin/assets/js/plugin/sortable/sortable.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.deleteTaskBtn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    document.getElementById('deleteTaskForm').setAttribute('action',
                        "{{ route('checklist.task.delete', ':id') }}".replace(':id', id));
                });
            });

            document.querySelectorAll('.deleteStageBtn').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const id = this.getAttribute('data-id');
                    document.getElementById('deleteStageForm').setAttribute('action',
                        "{{ route('checklist.stage.delete', ':id') }}".replace(':id', id));
                });
            });

            // Expand/collapse each stage (accordion) so long checklists don't need scrolling through everything
            document.querySelectorAll('.stage-toggle-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const card = this.closest('.manage-stage-card');
                    card.classList.toggle('collapsed');
                });
            });

            // Drag-and-drop reorder of stages
            const stageList = document.getElementById('stageList');
            if (stageList) {
                Sortable.create(stageList, {
                    handle: '.stage-drag-handle',
                    animation: 180,
                    ghostClass: 'stage-ghost',
                    onEnd: function () {
                        const ids = Array.from(stageList.querySelectorAll('.manage-stage-card')).map(el => el.getAttribute('data-id'));
                        fetch("{{ route('checklist.stages.reorder') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ checklist_ids: ids })
                        });
                    }
                });
            }

            // Drag-and-drop reorder of tasks within each stage
            document.querySelectorAll('.task-list').forEach(function (list) {
                Sortable.create(list, {
                    handle: '.task-drag-handle',
                    animation: 180,
                    ghostClass: 'task-ghost',
                    onEnd: function () {
                        const ids = Array.from(list.querySelectorAll('.task-item')).map(el => el.getAttribute('data-id'));
                        fetch("{{ route('checklist.tasks.reorder') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ task_ids: ids })
                        });
                    }
                });
            });

            const successAlert = document.querySelector('.alert-success');
            if (successAlert) {
                setTimeout(() => {
                    successAlert.classList.remove('show');
                    successAlert.classList.add('fade');
                }, 2000);
            }
        });
    </script>
@endsection
