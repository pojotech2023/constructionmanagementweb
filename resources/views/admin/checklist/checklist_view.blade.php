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
                    <li class="nav-item">
                        <a href="{{ route('sitemanagement.list') }}">Site</a>
                    </li>
                    <li class="separator"><i class="icon-arrow-right"></i></li>
                    <li class="nav-item"><a href="#">Check List</a></li>
                </ul>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                {{ session()->forget('success') }}
            @endif

            @php
                $formatChecklistName = function ($value) {
                    $text = trim((string) $value);
                    $text = preg_replace('/\s+/', ' ', $text);
                    $text = preg_replace('/\s*,\s*/', ', ', $text);
                    $text = preg_replace('/\s*&\s*/', ',&', $text);
                    $text = \Illuminate\Support\Str::title(\Illuminate\Support\Str::lower($text));

                    return str_replace(
                        ['Designs', 'Drawings', 'Pcc'],
                        ['Design', 'Drawing', 'PCC'],
                        $text
                    );
                };
            @endphp

            <div class="checklist-hero">
                <div>
                    <span class="hero-kicker">Site Checklist</span>
                    <h2>{{ $site->site_name }}</h2>
                    <p>Track every stage, task update, media submission, and approval status for this site.</p>
                </div>
                <div class="hero-action">
                    <i class="fa fa-check-square"></i>
                    <span>Quality Progress</span>
                </div>
            </div>

            <div class="checklist-board">
                @forelse($checklists as $checklist)
                    @php
                        $visibleTasks = session('role_name') == 'Admin'
                            ? $checklist->tasks
                            : $checklist->tasks;

                        $approvedCount = 0;
                        $rejectedCount = 0;
                        $pendingCount = 0;
                        $yetToWorkCount = 0;

                        foreach ($visibleTasks as $stageTask) {
                            $stageMedia = $stageTask->media
                                ->where('site_id', $site->id)
                                ->sortByDesc('id')
                                ->first();

                            if (!$stageMedia) {
                                $yetToWorkCount++;
                            } elseif (strtolower($stageMedia->status ?? '') === 'approved') {
                                $approvedCount++;
                            } elseif (strtolower($stageMedia->status ?? '') === 'rejected') {
                                $rejectedCount++;
                            } else {
                                $pendingCount++;
                            }
                        }

                        $taskCount = $visibleTasks->count();
                        $progress = $taskCount > 0 ? round(($approvedCount / $taskCount) * 100) : 0;
                    @endphp

                    @if($taskCount > 0)
                        <div class="checklist-stage-card">
                            <button class="checklist-stage-header" type="button" aria-expanded="false">
                                <div class="stage-title-wrap">
                                    <span class="stage-icon"><i class="fa fa-tasks"></i></span>
                                    <div>
                                        <h4>{{ $formatChecklistName($checklist->stage) }}</h4>
                                        <p>{{ $approvedCount }} of {{ $taskCount }} tasks approved</p>
                                    </div>
                                </div>
                                <div class="stage-progress-wrap">
                                    <span class="stage-pill approved">{{ $approvedCount }} Approved</span>
                                    @if($pendingCount > 0)
                                        <span class="stage-pill pending">{{ $pendingCount }} Pending</span>
                                    @endif
                                    @if($yetToWorkCount > 0)
                                        <span class="stage-pill yet-to-work">{{ $yetToWorkCount }} Yet to Work</span>
                                    @endif
                                    @if($rejectedCount > 0)
                                        <span class="stage-pill rejected">{{ $rejectedCount }} Rejected</span>
                                    @endif
                                    <span class="stage-toggle"><i class="fa fa-chevron-down"></i></span>
                                </div>
                            </button>

                            <div class="checklist-stage-content">
                                @php $previousApproved = true; @endphp

                                @foreach($visibleTasks as $task)
                                    @php
                                        $media = $task->media
                                            ->where('site_id', $site->id)
                                            ->sortByDesc('id')
                                            ->first();

                                        if (!$media) {
                                            $statusClass = 'yet-to-work';
                                            $statusLabel = 'Yet to Work';
                                        } else {
                                            $status = strtolower($media->status ?? '');
                                            $statusClass = match ($status) {
                                                'approved' => 'approved',
                                                'rejected' => 'rejected',
                                                default => 'pending',
                                            };
                                            $statusLabel = ucfirst($statusClass);
                                        }
                                        $isApproved = $media && strtolower($media->status) === 'approved';
                                    @endphp

                                    <div class="checklist-task-row {{ $statusClass }}">
                                        <span class="task-status-dot"></span>
                                        <div class="task-main">
                                            @if(session('role_name') == 'Supervisor')
                                                @if($previousApproved)
                                                    <a href="{{ route('task.create', ['siteId' => $site->id, 'taskId' => $task->id]) }}">
                                                        {{ $formatChecklistName($task->task_name) }}
                                                    </a>
                                                    <span>Update checklist progress</span>
                                                @else
                                                    <span class="locked-task" title="Complete previous task first">
                                                        {{ $formatChecklistName($task->task_name) }} <i class="fa fa-lock"></i>
                                                    </span>
                                                    <span>Previous task must be approved first</span>
                                                @endif

                                                @php $previousApproved = $isApproved; @endphp
                                            @else
                                                <a href="{{ route('admin.taskmedia.view', ['siteId' => $site->id, 'taskId' => $task->id]) }}">
                                                    {{ $formatChecklistName($task->task_name) }}
                                                </a>
                                                <span>View submitted images, videos, and remarks</span>
                                            @endif
                                        </div>
                                        <span class="task-status-label">{{ $statusLabel }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="empty-checklist">
                        <i class="fa fa-clipboard"></i>
                        <h4>No checklist found</h4>
                        <p>Add checklist stages to start tracking site work.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('.checklist-stage-header').forEach(button => {
                button.addEventListener('click', function() {
                    const card = this.closest('.checklist-stage-card');

                    document.querySelectorAll('.checklist-stage-card').forEach(item => {
                        if (item !== card) {
                            item.classList.remove('active');
                            item.querySelector('.checklist-stage-header').setAttribute('aria-expanded', 'false');
                        }
                    });

                    const isActive = card.classList.toggle('active');
                    this.setAttribute('aria-expanded', isActive ? 'true' : 'false');
                });
            });

            const firstStage = document.querySelector('.checklist-stage-card');
            if (firstStage) {
                firstStage.classList.add('active');
                firstStage.querySelector('.checklist-stage-header').setAttribute('aria-expanded', 'true');
            }
        });
    </script>

    <style>
        .page-header {
            padding-bottom: 14px;
            border-bottom: 1px solid #e7edf6;
            margin-bottom: 20px;
        }

        .checklist-hero {
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
            padding: 0;
            border-radius: 0;
            color: #087443;
            background: transparent;
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

        .checklist-hero h2 {
            margin: 0;
            color: #07164a;
            font-size: 24px;
            font-weight: 900;
        }

        .checklist-hero p {
            max-width: 680px;
            margin: 8px 0 0;
            color: #58667a;
            font-size: 15px;
            font-weight: 600;
            line-height: 1.6;
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
        }

        .checklist-board {
            display: grid;
            gap: 20px;
        }

        .checklist-stage-card {
            overflow: hidden;
            border: 1px solid #e8edf6;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 10px 26px rgba(20, 35, 70, 0.07);
        }

        .checklist-stage-header {
            width: 100%;
            border: 0;
            background: #fff;
            padding: 22px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            text-align: left;
            cursor: pointer;
        }

        .stage-title-wrap,
        .stage-progress-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
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
        }

        .stage-title-wrap h4 {
            margin: 0;
            color: #07164a;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 0;
        }

        .stage-title-wrap p {
            margin: 4px 0 0;
            color: #6b778c;
            font-weight: 600;
        }

        .stage-pill {
            border-radius: 12px;
            padding: 7px 12px;
            font-size: 12px;
            font-weight: 800;
        }

        .stage-pill.approved {
            color: #087443;
            background: #e8f8ef;
        }

        .stage-pill.pending {
            color: #946200;
            background: #fff4d8;
        }

        .stage-pill.yet-to-work {
            color: #506174;
            background: #eef3f8;
        }

        .stage-pill.rejected {
            color: #c82435;
            background: #ffe8eb;
        }

        .stage-toggle {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #246bfe;
            background: #eaf1ff;
            transition: transform 0.2s ease;
        }

        .checklist-stage-card.active .stage-toggle {
            transform: rotate(180deg);
        }

        .checklist-stage-content {
            display: none;
            padding: 18px;
            border-top: 1px solid #edf1f7;
            background: #fff;
        }

        .checklist-stage-card.active .checklist-stage-content {
            display: grid;
            gap: 12px;
        }

        .checklist-task-row {
            display: grid;
            grid-template-columns: 14px 1fr auto;
            align-items: center;
            gap: 14px;
            padding: 16px;
            border: 1px solid #e9eef6;
            border-radius: 8px;
            background: #fff;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .checklist-task-row:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(20, 35, 70, 0.08);
        }

        .task-status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #9aa6b2;
        }

        .checklist-task-row.approved .task-status-dot {
            background: #18b564;
        }

        .checklist-task-row.pending .task-status-dot {
            background: #f4b740;
        }

        .checklist-task-row.yet-to-work .task-status-dot {
            background: #9aa6b2;
        }

        .checklist-task-row.rejected .task-status-dot {
            background: #f05260;
        }

        .task-main a,
        .locked-task {
            display: inline-block;
            color: #0d1b4c;
            font-size: 15px;
            font-weight: 800;
            text-decoration: none !important;
        }

        .task-main a:hover {
            color: #246bfe;
        }

        .task-main span:last-child {
            display: block;
            margin-top: 3px;
            color: #7b8797;
            font-size: 13px;
            font-weight: 600;
        }

        .locked-task {
            color: #8b96a8;
            cursor: not-allowed;
        }

        .task-status-label {
            min-width: 86px;
            text-align: center;
            border-radius: 18px;
            padding: 8px 14px;
            color: #5b6678;
            background: #f1f4f8;
            font-size: 12px;
            font-weight: 800;
        }

        .checklist-task-row.approved .task-status-label {
            color: #087443;
            background: #e8f8ef;
        }

        .checklist-task-row.pending .task-status-label {
            color: #946200;
            background: #fff4d8;
        }

        .checklist-task-row.yet-to-work .task-status-label {
            color: #506174;
            background: #eef3f8;
        }

        .checklist-task-row.rejected .task-status-label {
            color: #c82435;
            background: #ffe8eb;
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
            .checklist-hero,
            .checklist-stage-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .hero-action {
                width: 100%;
            }

            .checklist-task-row {
                grid-template-columns: 14px 1fr;
            }

            .task-status-label {
                grid-column: 2;
                justify-self: flex-start;
            }
        }
    </style>
@endsection
