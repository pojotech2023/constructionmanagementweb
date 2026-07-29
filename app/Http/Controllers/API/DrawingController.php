<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Drawing;
use Illuminate\Support\Facades\Validator;

class DrawingController extends Controller
{
    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'site_id' => 'required|exists:sites,id',
        'attachments.*' => 'nullable|mimetypes:image/jpeg,image/png,application/pdf,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/acad,image/vnd.dwg|max:5120',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422);
    }

    $uploadedFiles = [];

    if ($request->hasFile('attachments')) {
        foreach ($request->file('attachments') as $file) {
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('drawings', $filename, 'public');

            $drawing = Drawing::create([
                'site_id'   => $request->site_id,
                'file_path' => $path,
                'file_name' => $filename,
                'file_type' => $file->getClientMimeType(),
            ]);

            $uploadedFiles[] = $drawing;
        }
    }

    return response()->json([
        'status'  => true,
        'message' => 'Drawings uploaded successfully.',
        'data'    => $uploadedFiles,
    ], 201);
}


  public function getDrawingsBySite($site_id)
{
    $drawings = Drawing::where('site_id', $site_id)->get();

    $data = $drawings->map(function ($drawing) {
        // Public URL (for viewing in browser)
        $viewUrl = asset('storage/' . $drawing->file_path);

        // Download URL (forces browser to download)
        $downloadUrl = route('drawings.download', ['id' => $drawing->id]);

        return [
            'drawing_id' => $drawing->id,
            'drawing_name' => $drawing->file_name,
            'drawing_view_url' =>  $drawing->file_path,
            'drawing_download_url' => $downloadUrl,
        ];
    });

    return response()->json([
        'status' => true,
        'message' => 'Drawings for site fetched successfully.',
        'site_id' => (int) $site_id,
        'data' => $data
    ]);
}
public function download($id)
{
    $drawing = Drawing::findOrFail($id);
    $filePath = storage_path('app/public/' . $drawing->file_path);

    if (!file_exists($filePath)) {
        return response()->json(['status' => false, 'message' => 'File not found.'], 404);
    }

    return response()->download($filePath, $drawing->file_name);
}

public function shareWhatsapp(Request $request, $id)
{
    $drawing = Drawing::findOrFail($id);

    $validator = Validator::make($request->all(), [
        'mobile_no' => 'required|digits:10',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422);
    }

    $fileUrl = asset('storage/' . $drawing->file_path);

    $message = "*POJO INFRA 360*\n"
        . "Drawing: {$drawing->file_name}\n"
        . "Link: {$fileUrl}";

    $whatsappUrl = 'https://wa.me/91' . $request->mobile_no . '?text=' . urlencode($message);

    return response()->json([
        'status'       => true,
        'message'      => 'WhatsApp share link generated successfully.',
        'whatsapp_url' => $whatsappUrl,
    ]);
}

public function destroy($id)
{
    // Find the drawing record
    $drawing = Drawing::find($id);

    if (!$drawing) {
        return response()->json(['status' => false, 'message' => 'Drawing not found.'], 404);
    }

    // Get the full file path
    $filePath = storage_path('app/public/' . $drawing->file_path);

    // Delete the file if it exists
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    // Delete the database record
    $drawing->delete();

    return response()->json(['status' => true, 'message' => 'Drawing deleted successfully.']);
}


}
