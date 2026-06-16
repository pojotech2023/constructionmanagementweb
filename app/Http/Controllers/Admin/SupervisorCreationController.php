<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoleMapping;
use App\Models\SupervisorPermission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SupervisorCreationController extends Controller
{
    public function index()
    {
        $supervisors = User::whereHas('roles', function ($query) {
            $query->where('role_name', 'Supervisor');
        })->get();

        $modules = $this->permissionModules();

        return view('admin.menus.supervisor.user_create', compact('supervisors', 'modules'));
    }

    public function store(Request $request)
    {
        //dd($request->all());
        $validate = Validator::make($request->all(), [
            'name'          => 'required|string',
            'mobile_no'     => 'required|numeric|digits:10',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|min:6|confirmed'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $supervisor = User::create([
            'name' => $request->name,
            'mobile_no' => $request->mobile_no,
            'email' => $request->email,
            'password' => Crypt::encryptString($request->password),
            'created_by'  => auth('admin')->id(),
        ]);

        $supervisorId = $supervisor->id;

        $role_mapping = RoleMapping::create([
            'user_id' => $supervisorId,
            'role_id' => 2
        ]);

        return redirect()->back()->with('success', 'Supervisor created successfully!');
    }

    public function update(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'user_id'       => 'required|exists:users,id',
            'name'          => 'required|string',
            'mobile_no'     => 'required|numeric|digits:10',
            'email' => 'required|email|unique:users,email,' . $request->user_id,
            'password'      => 'required|min:6|confirmed'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $supervisor = User::findOrFail($request->user_id);

        $supervisor->update([
            'name'  => $request->name,
            'mobile_no' => $request->mobile_no,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'updated_by'  => auth('admin')->id(),
        ]);

        return redirect()->back()->with('success', 'Supervisor updated successfully!');
    }

    public function delete($id)
    {
        $supervisor = User::findOrFail($id);
        $supervisor->delete();
        return back()->with('success', 'Supervisor Deleted Successfully!');
    }

    public function getPermissions($id)
    {
        $supervisor = User::findOrFail($id);
        $modules    = $this->permissionModules();

        $saved = SupervisorPermission::where('supervisor_id', $id)
            ->get()
            ->keyBy(fn($p) => $p->module . '.' . $p->action);

        return view('admin.menus.supervisor.permissions', compact('supervisor', 'modules', 'saved'));
    }

    public function savePermissions(Request $request, $id)
    {
        $modules = $this->permissionModules();

        foreach ($modules as $moduleKey => $module) {
            foreach (array_keys($module['actions']) as $actionKey) {
                $isAllowed = $request->has("permissions.{$moduleKey}.{$actionKey}");

                SupervisorPermission::updateOrCreate(
                    ['supervisor_id' => $id, 'module' => $moduleKey, 'action' => $actionKey],
                    ['is_allowed' => $isAllowed]
                );
            }
        }

        return redirect()->back()->with('success', 'Permissions saved successfully!');
    }

    public static function permissionModules(): array
    {
        return [
            'site_management' => [
                'label' => 'Site Management',
                'actions' => [
                    'add_site'               => ['label' => 'Add Site',            'parent' => null],
                    'view_site'              => ['label' => 'View Site',            'parent' => null],
                    'view_today_attendance'  => ['label' => 'Today Attendance',     'parent' => 'view_site'],
                    'view_materials'         => ['label' => 'Materials',            'parent' => 'view_site'],
                    'view_subcontractor'     => ['label' => 'SubContractor',        'parent' => 'view_site'],
                    'view_payment_status'    => ['label' => 'Payment Status',       'parent' => 'view_site'],
                    'view_checklist'         => ['label' => 'Checklist',            'parent' => 'view_site'],
                    'view_tickets'           => ['label' => 'Tickets',              'parent' => 'view_site'],
                    'view_drawing'           => ['label' => 'Drawing',              'parent' => 'view_site'],
                    'edit_site'              => ['label' => 'Edit Site',            'parent' => 'view_site'],
                    'delete_site'            => ['label' => 'Delete Site',          'parent' => 'view_site'],
                ],
            ],

            'quotation' => [
                'label' => 'Quotation',
                'actions' => [
                    'add_quotation'    => ['label' => 'Add',    'parent' => null],
                    'edit_quotation'   => ['label' => 'Edit',   'parent' => null],
                    'delete_quotation' => ['label' => 'Delete', 'parent' => null],
                ],
            ],

            'customer_management' => [
                'label' => 'Customer Management',
                'actions' => [
                    'add_customer'    => ['label' => 'Add',    'parent' => null],
                    'edit_customer'   => ['label' => 'Edit',   'parent' => null],
                    'delete_customer' => ['label' => 'Delete', 'parent' => null],
                ],
            ],

            'vendor_management' => [
                'label' => 'Vendor Management',
                'actions' => [
                    'add_vendor'          => ['label' => 'Add',          'parent' => null],
                    'edit_vendor'         => ['label' => 'Edit',         'parent' => null],
                    'delete_vendor'       => ['label' => 'Delete',       'parent' => null],
                    'view_amount'         => ['label' => 'View Amount',  'parent' => null],
                    'add_payment'         => ['label' => 'Add Payment',  'parent' => 'view_amount'],
                    'payment_history'     => ['label' => 'Payment History', 'parent' => 'view_amount'],
                ],
            ],

            'subcontractor_management' => [
                'label' => 'Subcontractor Management',
                'actions' => [
                    'add_subcontractor'      => ['label' => 'Add',          'parent' => null],
                    'edit_subcontractor'     => ['label' => 'Edit',         'parent' => null],
                    'delete_subcontractor'   => ['label' => 'Delete',       'parent' => null],
                    'view_amount'            => ['label' => 'View Amount',  'parent' => null],
                    'add_payment'            => ['label' => 'Add Payment',  'parent' => 'view_amount'],
                    'payment_history'        => ['label' => 'Payment History', 'parent' => 'view_amount'],
                ],
            ],
        ];
    }
}