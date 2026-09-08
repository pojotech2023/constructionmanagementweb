<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TermsCondition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TermsConditionController extends Controller
{
    // List/manage all terms & conditions templates
    public function manage()
    {
        $termsConditions = TermsCondition::orderBy('title')->get();

        return view('admin.menus.terms_condition.terms_condition_manage', compact('termsConditions'));
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        if ($validate->fails()) {
            return redirect()->route('terms-condition.manage')
                ->withErrors($validate)
                ->withInput();
        }

        TermsCondition::create([
            'title' => $request->title,
            'description' => $request->description,
            'created_by' => auth('admin')->id(),
        ]);

        return redirect()->route('terms-condition.manage')->with('success', 'Terms & Conditions template added successfully.');
    }

    public function update(Request $request, $id)
    {
        $termsCondition = TermsCondition::findOrFail($id);

        $validate = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        if ($validate->fails()) {
            return redirect()->route('terms-condition.manage')
                ->withErrors($validate)
                ->withInput();
        }

        $termsCondition->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return redirect()->route('terms-condition.manage')->with('success', 'Terms & Conditions template updated successfully.');
    }

    public function destroy($id)
    {
        TermsCondition::findOrFail($id)->delete();

        return redirect()->route('terms-condition.manage')->with('success', 'Terms & Conditions template deleted successfully.');
    }

    // Autocomplete: returns titles matching the typed letters (used in Quotation / Sales Bill / Purchase Bill forms)
    public function searchTitles(Request $request)
    {
        $term = trim((string) $request->query('term'));

        if ($term === '') {
            return response()->json(['data' => []]);
        }

        $results = TermsCondition::where('title', 'like', $term . '%')
            ->orderBy('title')
            ->limit(10)
            ->get(['id', 'title']);

        return response()->json(['data' => $results]);
    }

    // Returns the description for a given title (exact match) so it can auto-fill the terms & conditions field
    public function getDescription(Request $request)
    {
        $title = trim((string) $request->query('title'));

        $termsCondition = TermsCondition::where('title', $title)->first();

        return response()->json([
            'description' => $termsCondition->description ?? '',
        ]);
    }
}

