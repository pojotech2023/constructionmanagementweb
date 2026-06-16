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

        return back()->with('success', 'Drawings uploaded successfully.');
    }
    public function destroy($id)
{
    $drawing = Drawing::findOrFail($id);

    // Delete file from storage
    if (\Storage::disk('public')->exists($drawing->file_path)) {
        \Storage::disk('public')->delete($drawing->file_path);
    }

    $drawing->delete();

    return back()->with('success', 'Drawing deleted successfully.');
}

//Drawing api 
 

}
