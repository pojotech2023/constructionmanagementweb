<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Agent;
use Illuminate\Support\Facades\Validator;

class AgentController extends Controller
{
    public function index()
    {
        $agents = Agent::where('is_inactive', 0)
            ->orderBy('id', 'desc')->get();

        return response()->json([
            'respnse code' => 200,
            'data' => $agents,
            'status' => true,
            'message' => 'Agents fetched successfully!',
        ]);
    }

    public function store(Request $request)
    {

        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'company_name' => 'required',
            'mobile_no' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validate->errors()
            ], 422);
        }

        $agent = Agent::create([
            'name' => $request->name,
            'company_name' => $request->company_name,
            'mobile_no' => $request->mobile_no,
            'created_by' => auth('api')->id(),

        ]);

        return response()->json([
            'response code' => 200,
            'data' => $agent,
            'status' => true,
            'message' => 'Agent created successfully!',
        ]);
    }

    public function update(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'name'  => 'required',
            'company_name' => 'required',
            'mobile_no' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validate->errors()
            ], 422);
        }

        $agent = Agent::findOrFail($id);

        $agent->update([
            'name'      => $request->name,
            'company_name' => $request->company_name,
            'mobile_no' => $request->mobile_no,
            'updated_by' => auth('api')->id(),

        ]);

        return response()->json([
            'response code' => 200,
            'data' => $agent,
            'status' => true,
            'message' => 'Agent updated successfully!',
        ]);
    }

    public function delete($id)
    {
        $agent = Agent::findOrFail($id);
        $agent->update(['is_inactive' => 1]);

        return response()->json([
            'response code' => 200,
            'data' => $agent,
            'status' => true,
            'message' => 'Agent Deleted Successfully!',
        ]);
    }
}
