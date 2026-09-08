<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TermsCondition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TermsConditionController extends Controller
{
    /**
     * List all terms & conditions templates.
     */
    public function index()
    {
        $termsConditions = TermsCondition::orderBy('title')->get();

        return response()->json([
            'status' => true,
            'message' => 'Terms & Conditions templates fetched successfully.',
            'data' => $termsConditions,
        ]);
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        if ($validate->fails()) {
            return response()->json(['status' => false, 'errors' => $validate->errors()], 422);
        }

        $termsCondition = TermsCondition::create([
            'title' => $request->title,
            'description' => $request->description,
            'created_by' => auth('admin')->id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Terms & Conditions template added successfully.',
            'data' => $termsCondition,
        ]);
    }

    public function update(Request $request, $id)
    {
        $termsCondition = TermsCondition::find($id);

        if (!$termsCondition) {
            return response()->json(['status' => false, 'message' => 'Template not found'], 404);
        }

        $validate = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        if ($validate->fails()) {
            return response()->json(['status' => false, 'errors' => $validate->errors()], 422);
        }

        $termsCondition->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Terms & Conditions template updated successfully.',
            'data' => $termsCondition,
        ]);
    }

    public function destroy($id)
    {
        $termsCondition = TermsCondition::find($id);

        if (!$termsCondition) {
            return response()->json(['status' => false, 'message' => 'Template not found'], 404);
        }

        $termsCondition->delete();

        return response()->json([
            'status' => true,
            'message' => 'Terms & Conditions template deleted successfully.',
        ]);
    }

    /**
     * Autocomplete: returns titles matching the typed letters.
     */
    public function searchTitles(Request $request)
    {
        $term = trim((string) $request->query('term'));

        if ($term === '') {
            return response()->json(['status' => true, 'data' => []]);
        }

        $results = TermsCondition::where('title', 'like', $term . '%')
            ->orderBy('title')
            ->limit(10)
            ->get(['id', 'title']);

        return response()->json(['status' => true, 'data' => $results]);
    }

    /**
     * Returns the description for a given title (exact match) so it can auto-fill
     * the terms & conditions field in Quotation / Sales Bill / Purchase Bill.
     */
    public function getDescription(Request $request)
    {
        $title = trim((string) $request->query('title'));

        $termsCondition = TermsCondition::where('title', $title)->first();

        return response()->json([
            'status' => true,
            'description' => $termsCondition->description ?? '',
        ]);
    }
}

