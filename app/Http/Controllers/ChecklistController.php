<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Checklist;
use App\Models\task;
use App\Models\Site;
use App\Models\TaskMedia;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
class ChecklistController extends Controller
{
    public function store(Request $request)
{
    $request->validate([
        'stage' => 'required|string|max:255',
        'task_list' => 'required|array',
        'task_list.*' => 'required|string|max:255',
    ]);

    $checklist = Checklist::create([
        'stage' => $request->stage,
    ]);

    foreach ($request->task_list as $task) {
        $checklist->tasks()->create([
            'task_name' => $task,
        ]);
    }



    return redirect()->back()->with('success', 'Checklist created successfully.');
}

public function update(Request $request, $id)
{
    $request->validate([
        'stage' => 'required|string|max:255',
        'task_list' => 'required|array',
        'task_list.*' => 'required|string|max:255',
    ]);

    $checklist = Checklist::findOrFail($id);
    $checklist->update([
        'stage' => $request->stage,
    ]);

    // Optional: delete existing tasks before updating
    $checklist->tasks()->delete();

    // Re-insert tasks
    foreach ($request->task_list as $task) {
        $checklist->tasks()->create([
            'task_name' => $task,
        ]);
    }

    return redirect()->back()->with('success', 'Checklist updated successfully.');
}
public function index($siteId)
{
    $site = Site::findOrFail($siteId);
    
    $checklists = Checklist::with(['tasks' => function ($query) use ($siteId) {
        $query->with(['media' => function ($q) use ($siteId) {
            $q->where('site_id', $siteId);
        }]);
    }])->get();

    $mediaTaskIdsForSite = TaskMedia::where('site_id', $siteId)
        ->pluck('task_id')
        ->toArray();

    return view('admin.checklist.checklist_view', compact('checklists', 'mediaTaskIdsForSite', 'site'));
}


public function taskstore(Request $request)
{
    $request->validate([
        'task_id' => 'required|exists:tasks,id',
        'site_id' => 'required',
        'task_images.*' => 'nullable|image|mimes:jpg,jpeg,png,gif',
        'task_videos.*' => 'nullable|mimetypes:video/mp4,video/avi,video/mpeg',
        'remarks' => 'nullable|string',
    ]);

    // Check if at least one field is filled
    $hasImages = $request->hasFile('task_images');
    $hasVideos = $request->hasFile('task_videos');
    $hasRemarks = $request->filled('remarks');

    if (!$hasImages && !$hasVideos && !$hasRemarks) {
        return back()->withErrors(['error' => 'Please provide at least one: image, video, or remark.'])->withInput();
    }

    // Handle images
    if ($hasImages) {
        foreach ($request->file('task_images') as $image) {
            $imagePath = $image->store('uploads/images', 'public');

            TaskMedia::create([
                'site_id' => $request->site_id,
                'task_id' => $request->task_id,
                'image_path' => $imagePath,
                'remarks' => $request->remarks,
            ]);
        }
    }

    // Handle videos
    if ($hasVideos) {
        foreach ($request->file('task_videos') as $video) {
            $videoPath = $video->store('uploads/videos', 'public');

            TaskMedia::create([
                'site_id' => $request->site_id,
                'task_id' => $request->task_id,
                'video_path' => $videoPath,
                'remarks' => $request->remarks,
            ]);
        }
    }

    // If only remarks provided (without files), still store it
    if (!$hasImages && !$hasVideos && $hasRemarks) {
        TaskMedia::create([
            'site_id' => $request->site_id,
            'task_id' => $request->task_id,
            'remarks' => $request->remarks,
        ]);
    }

    return back()->with('success', 'Submitted successfully!');
}



public function deleteByRemarks($id, $siteId)
{
    // Find the selected media record first
    $selectedMedia = TaskMedia::find($id);

    if (!$selectedMedia) {
        return back()->with('error', 'Media record not found.');
    }

    // Get the remark value for this record
    $remark = $selectedMedia->remarks;

    // Fetch all media records with the same remark and site_id
    $mediaItems = TaskMedia::where('site_id', $siteId)
        ->where('remarks', $remark)
        ->get();

    if ($mediaItems->isEmpty()) {
        return back()->with('error', 'No media found for this remark.');
    }

    // Loop and delete files + records
    foreach ($mediaItems as $media) {
        if ($media->image_path && Storage::disk('public')->exists($media->image_path)) {
            Storage::disk('public')->delete($media->image_path);
        }

        if ($media->video_path && Storage::disk('public')->exists($media->video_path)) {
            Storage::disk('public')->delete($media->video_path);
        }

        $media->delete();
    }

    return back()->with('success', 'All media with this remark deleted successfully.');
}



  //admin view page

public function viewTaskMedia($siteId, $taskId)
{
    // Filter media by both task_id and site_id
    $taskMedia = TaskMedia::where('task_id', $taskId)
        ->where('site_id', $siteId)
        ->get();

    $task = Task::findOrFail($taskId);

    return view('admin.checklist.task_view', compact('taskMedia', 'task', 'siteId'));
}

public function updateTaskMedia(Request $request, $id)
{
    $request->validate([
        'admin_remark' => 'nullable|string',
        'status' => 'required|in:approved,rejected',
    ]);

    $media = TaskMedia::findOrFail($id);
    $media->status = $request->status;

    // If rejected → save remark, else clear it
    $media->admin_remark = $request->status === 'rejected' ? $request->admin_remark : null;

    // Save admin who updated
    $media->updated_by = auth()->user()->name ?? 'Admin';

    $media->save();
    // Redirect to checklist with correct site ID
    return redirect()
        ->route('checklist', ['siteId' => $media->site_id])
        ->with('success', 'Admin review submitted successfully.');
}


public function taskcreate($siteId, $taskId)
{
    $task = Task::findOrFail($taskId);
    $site = Site::findOrFail($siteId);
  $mediaItems = \App\Models\TaskMedia::where('site_id', $siteId)
        ->where('task_id', $taskId)
        ->get();
    // Get only media related to current site and task
    $latestMedia = TaskMedia::where('task_id', $taskId)
    ->where('site_id', $siteId)
    ->orderByDesc('id') // or 'created_at' if you prefer
    ->first();

  return view('admin.checklist.task_update', compact('task', 'site', 'latestMedia', 'mediaItems'));


}

 // api for checklist view
public function getChecklistForSite($siteId)
{
    $checklists = Checklist::with(['tasks.media' => function ($query) use ($siteId) {
        $query->where('site_id', $siteId)->latest('id');
    }])->get();

    $response = collect();

    foreach ($checklists as $checklist) {

        $tasksResponse = collect();

        $firstYetToWorkGiven = false; // track first YTW task

        foreach ($checklist->tasks as $task) {

            $latestMedia = $task->media
                ->where('site_id', $siteId)
                ->sortByDesc('id')
                ->first();

            // Defaults
            $status = -1;
            $statusLabel = "Yet to Work";
            $canOpen = false; // default

            if ($latestMedia) {
                // If media exists → user can open
                $canOpen = true;

                $mediaStatus = strtolower($latestMedia->status ?? '');

                if ($mediaStatus === 'approved') {
                    $status = 1;
                    $statusLabel = "Approved";
                } elseif ($mediaStatus === 'rejected') {
                    $status = 2;
                    $statusLabel = "Rejected";
                } else {
                    $status = 0;
                    $statusLabel = "Pending";
                }

            } else {
                // NO MEDIA (Yet to Work)

                if (!$firstYetToWorkGiven) {
                    // first YTW → can open
                    $canOpen = true;
                    $firstYetToWorkGiven = true;
                } else {
                    // all other YTW → cannot open
                    $canOpen = false;
                }
            }

            $tasksResponse->push([
                'canOpen'      => $canOpen,
                'task_id'      => $task->id,
                'task_name'    => $task->task_name,
                'status'       => $status,
                'status_label' => $statusLabel,
                'supervisor_remarks' => $latestMedia->remarks ?? null,
                'image'        => $latestMedia->image_path ?? null,
                'video'        => $latestMedia->video_path ?? null,
                'admin_remark' => $latestMedia->admin_remark ?? null,
                'updated_by' => $latestMedia->updated_by ?? null,
            ]);
        }

        $response->push([
            'checklist_id' => $checklist->id,
            'stage' => $checklist->stage,
            'tasks' => $tasksResponse
        ]);
    }

    return response()->json([
        'site_id'    => $siteId,
        'checklists' => $response
    ]);
}



//supervisor checklist
public function supervisorChecklist($siteId)
{
    $checklists = Checklist::with(['tasks.media' => function ($query) use ($siteId) {
        $query->where('site_id', $siteId)->latest('id');
    }])->get();

    $response = $checklists->map(function ($checklist) use ($siteId) {
        return [
            'checklist_id' => $checklist->id,
            'stage' => $checklist->stage,
            'tasks' => $checklist->tasks->map(function ($task) use ($siteId) {
                $latestMedia = $task->media
                    ->where('site_id', $siteId)
                    ->sortByDesc('id')
                    ->first();

                // default
                $status = -1;
                $statusLabel = "Yet to Work";

                if ($latestMedia) {
                    if (strtolower($latestMedia->status ?? '') === 'approved') {
                        $status = 1;
                        $statusLabel = "Approved";
                    } elseif (strtolower($latestMedia->status ?? '') === 'rejected') {
                        $status = 2;
                        $statusLabel = "Rejected";
                    } else {
                        $status = 0;
                        $statusLabel = "Pending";
                    }
                }

                return [
                    'task_id'      => $task->id,
                    'task_name'    => $task->task_name,
                    'status'       => $status,
                    'status_label' => $statusLabel,
                    'remarks'      => $latestMedia->remarks ?? null,
                    'image'        => $latestMedia->image_path ?? null,
                    'video'        => $latestMedia->video_path ?? null,
                ];
            }),
        ];
    });

    return response()->json([
        'site_id'    => $siteId,
        'checklists' => $response
    ]);
}


public function taskmediastore(Request $request)
{
    $request->validate([
        'site_id'   => 'required|integer|exists:sites,id',
        'task_id'   => 'required|integer|exists:tasks,id',
        'remarks'   => 'nullable|string',
        'images.*'  => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        'videos.*'  => 'nullable|file|mimes:mp4,mov,avi|max:10240',
    ]);

    $siteId = $request->site_id;
    $taskId = $request->task_id;

    // Store images
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            $path = $image->store('uploads/images', 'public');
            TaskMedia::create([
                'site_id' => $siteId,
                'task_id' => $taskId,
                'image_path' => $path,
                'remarks' => $request->remarks,
            ]);
        }
    }

    // Store videos
    if ($request->hasFile('videos')) {
        foreach ($request->file('videos') as $video) {
            $path = $video->store('uploads/videos', 'public');
            TaskMedia::create([
                'site_id' => $siteId,
                'task_id' => $taskId,
                'video_path' => $path,
                'remarks' => $request->remarks,
            ]);
        }
    }

    // Store remarks only
    if (!$request->hasFile('images') && !$request->hasFile('videos') && $request->filled('remarks')) {
        TaskMedia::create([
            'site_id' => $siteId,
            'task_id' => $taskId,
            'remarks' => $request->remarks,
        ]);
    }

    return response()->json([
        'status' => true,
        'message' => 'Task media inserted successfully',
    ]);
}


// api admin reply
public function adminUpdateTask(Request $request)
{
    try {
        $input = $request->json()->all() ?: $request->all();

        $validator = Validator::make($input, [
            'site_id'      => 'required|integer|exists:sites,id',
            'task_id'      => 'required|integer|exists:tasks,id',
            'admin_remark' => 'nullable|string',
            'status'       => 'required|in:approved,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $admin  = auth()->user();
        $siteId = $input['site_id'];
        $taskId = $input['task_id'];

        $taskMedia = TaskMedia::where('site_id', $siteId)
            ->where('task_id', $taskId)
            ->get();

        if ($taskMedia->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'No media found for this site and task.'
            ], 404);
        }

        foreach ($taskMedia as $media) {
            $media->update([
                'admin_remark' => $input['admin_remark'],
                'status'       => $input['status'],
                'updated_by'   => $admin->name ?? $admin->email,
            ]);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Admin remark and status updated successfully.',
            'data'    => [
                'updated_by'   => $admin->name ?? $admin->email,
                'site_id'      => $siteId,
                'task_id'      => $taskId,
                'admin_remark' => $input['admin_remark'],
                'status'       => $input['status'],
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status'  => false,
            'message' => 'Something went wrong.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}



 public function getTaskMedia(Request $request)
{
    // Validate input
    $request->validate([
        'site_id' => 'required|integer',
        'task_id' => 'required|integer',
    ]);

    // Fetch matching records
    $taskMedias = TaskMedia::where('site_id', $request->site_id)
        ->where('task_id', $request->task_id)
        ->get();

    // Map response data
    $response = $taskMedias->map(function ($item) {

        // Determine admin status code
        $adminStatus = 0; // default: waiting
        if ($item->admin_remark) {
            $remark = strtolower($item->admin_remark);
            if (str_contains($remark, 'approved')) {
                $adminStatus = 1;
            } elseif (str_contains($remark, 'rejected')) {
                $adminStatus = -1;
            }
        }

        return [
            'id' => $item->id,
            'site_id' => $item->site_id,
            'task_id' => $item->task_id,
            'image_path' => $item->image_path,
            'video_path' => $item->video_path,
            'supervisor_remarks' => $item->remarks,
            'admin_remark' => $item->admin_remark ?? 'Waiting for admin response',
            'admin_status' => $adminStatus,
            'status' => $item->status,
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
        ];
    });

    return response()->json([
        'success' => true,
        'message' => 'Admin, Supervisor Remarks Fetched Successfully.',
        'data' => $response
    ], 200);
}


}
