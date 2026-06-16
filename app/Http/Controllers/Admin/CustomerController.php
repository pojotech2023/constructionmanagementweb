<?php

namespace App\Http\Controllers\Admin;

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

        return view('admin.menus.customer.customer_managment', compact('customers', 'reminders'));
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view('admin.menus.customer.customer_update', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'site_id'       => 'required|exists:sites,id',
            'name'          => 'required|string',
            'mobile_no'     => 'required|numeric|digits:10',
            'email'         => 'required|email|unique:customers,email,' . $id,
            'dob'           => 'required',
            'address'       => 'required|string'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $customer = Customer::findOrFail($id);

        $customer->update([
            'site_id' => $request->site_id,
            'name' => $request->name,
            'mobile_no' => $request->mobile_no,
            'email' => $request->email,
            'dob' => $request->dob,
            'address' => $request->address,
            'updated_by'  => auth('admin')->id(),
        ]);

        return redirect()->back()->with('success', 'Customer updated successfully!');
    }

    public function delete($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->update(['is_inactive' => 1]);
        return back()->with('success', 'Customer deleted successfully !');
    }
}
