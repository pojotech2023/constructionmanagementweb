<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AgentController extends Controller
{
    public function index()
    {
        $agents = Agent::where('is_inactive', 0)
            ->orderBy('id', 'desc')->get();
        return view('admin.menus.property.agent_management', compact('agents'));
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'company_name' => 'required',
            'mobile_no' => 'required'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $agent = Agent::create([
            'name' => $request->name,
            'company_name' => $request->company_name,
            'mobile_no' => $request->mobile_no,
            'created_by'  => auth('admin')->id(),
        ]);

        return redirect()->back()->with('success', 'Agent created successfully!');
    }

    public function update(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'agent_id' => 'required|exists:agents,id',
            'name'  => 'required',
            'company_name' => 'required',
            'mobile_no' => 'required'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $agent = Agent::findOrFail($request->agent_id);

        $agent->update([
            'name'      => $request->name,
            'company_name' => $request->company_name,
            'mobile_no' => $request->mobile_no,
            'updated_by'  => auth('admin')->id(),
        ]);

        return redirect()->back()->with('success', 'Agent updated successfully!');
    }

    public function delete($id)
    {
        $agent = Agent::findOrFail($id);
        $agent->update(['is_inactive' => 1]);
        return back()->with('success', 'Agent Deleted Successfully!');
    }
}
