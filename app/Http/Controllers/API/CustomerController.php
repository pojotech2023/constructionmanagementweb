<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::with('site')
            ->where('is_inactive', 0)
            ->orderBy('id', 'desc')->get();

         $tomorrow = now()->addDay()->format('m-d'); // format month-day

        $reminders = [];

        foreach ($customers as $customer) {
            if ($customer->dob && Carbon::parse($customer->dob)->format('m-d') === $tomorrow) {
                $reminders[] = [
                    'name' => $customer->name,
                    'type' => 'Birthday',
                    'date' => $customer->dob,
                ];
            }

            if ($customer->marriage_date && Carbon::parse($customer->marriage_date)->format('m-d') === $tomorrow) {
                $reminders[] = [
                    'name' => $customer->name,
                    'type' => 'Marriage',
                    'date' => $customer->marriage_date,
                ];
            }
        }

        return response()->json([
            'response code' => 200,
            'data' => [
                'customers' => $customers,
                'reminders' => $reminders,
            ],
            'status' => true,
            'message' => 'Customer fetched successfully!',
        ]);
    }

public function update(Request $request)
{
    $validate = Validator::make($request->all(), [
        'id'            => 'required|exists:customers,id',
        'site_id'       => 'required|exists:sites,id',
        'name'          => 'required|string',
        'mobile_no'     => 'required|numeric|digits:10',
        'email'         => 'required|email',
        'dob'           => 'required',
        'address'       => 'required|string'
    ]);

    if ($validate->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validate->errors()
        ], 422);
    }

    $customer = Customer::findOrFail($request->id);

    $dob = Carbon::parse($request->dob)->format('Y-m-d');

    $customer->update([
        'site_id'    => $request->site_id,
        'name'       => $request->name,
        'mobile_no'  => $request->mobile_no,
        'email'      => $request->email,
        'dob'        => $dob,
        'address'    => $request->address,
        'updated_by' => auth('api')->id(),
    ]);

    return response()->json([
        'response_code' => 200,
        'status' => true,
        'message' => 'Customer updated successfully!',
        'data' => $customer
    ]);
}

    public function delete($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->update(['is_inactive' => 1]);

        return response()->json([
            'response code' => 200,
            'data' => $customer,
            'status' => true,
            'message' => 'Customer deleted successfully!!',
        ]);
    }
}
