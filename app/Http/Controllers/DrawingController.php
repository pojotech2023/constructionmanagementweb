<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Drawing;
use App\Models\Site;

class DrawingController extends Controller

{

    public function drawingview($site_id)
{
    $site = Site::findOrFail($site_id);
    $drawings = $site->drawings; 
    return view('admin.menus.drawing.drawing-add', compact('site','drawings'));
}

     public function store(Request $request)
    {
        $request->validate([
            'site_id' => 'required|exists:sites,id',
          'attachments.*' => 'nullable|mimes:jpg,jpeg,png,pdf,xls,xlsx,dwg|max:2048',

        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('drawings', $filename, 'public');

                Drawing::create([
                    'site_id' => $request->site_id,
                    'file_path' => $path,
                    'file_name' => $filename,
                    'file_type' => $file->getClientMimeType(),
                ]);
            }
        }

        return redirect()->route('drawing', $request->site_id)->with('success', 'Drawings uploaded successfully.');
    }
    public function destroy($id)
{
    $drawing = Drawing::findOrFail($id);
    $siteId = $drawing->site_id;

    // Delete file from storage
    if (\Storage::disk('public')->exists($drawing->file_path)) {
        \Storage::disk('public')->delete($drawing->file_path);
    }

    $drawing->delete();

    // Redirect explicitly to this site's drawing page instead of back() — back()
    // depends on the Referer header / session-tracked previous URL, which is
    // unreliable in incognito/private browsing and was sending users to a stale
    // or invalid URL (404) after deleting.
    return redirect()->route('drawing', $siteId)->with('success', 'Drawing deleted successfully.');
}

//Drawing api 
 

}
