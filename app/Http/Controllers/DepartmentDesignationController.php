<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentDesignationController extends Controller
{
    /**
     * Set up middleware to authorize can_manage_staff permission.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $admin = Auth::user();
            $permissions = is_array($admin->permissions) ? $admin->permissions : json_decode($admin->permissions, true) ?? [];
            if ($admin->role !== 'super_admin' && !in_array('can_manage_staff', $permissions)) {
                abort(403, 'Unauthorized');
            }
            return $next($request);
        });
    }

    /**
     * Store a newly created department.
     */
    public function storeDepartment(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        Department::create([
            'name' => strip_tags($request->name),
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', 'নতুন বিভাগ সফলভাবে তৈরি হয়েছে।');
    }

    /**
     * Remove the specified department.
     */
    public function destroyDepartment($id)
    {
        $department = Department::where('user_id', Auth::id())->findOrFail($id);
        $department->delete();

        return back()->with('success', 'বিভাগটি সফলভাবে মুছে ফেলা হয়েছে।');
    }

    /**
     * Store a newly created designation.
     */
    public function storeDesignation(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'department_id' => 'required|exists:departments,id',
        ]);

        // Verify the department belongs to the logged-in admin
        $department = Department::where('user_id', Auth::id())->findOrFail($request->department_id);

        Designation::create([
            'name' => strip_tags($request->name),
            'department_id' => $department->id,
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', 'নতুন পদবী সফলভাবে তৈরি হয়েছে।');
    }

    /**
     * Remove the specified designation.
     */
    public function destroyDesignation($id)
    {
        $designation = Designation::where('user_id', Auth::id())->findOrFail($id);
        $designation->delete();

        return back()->with('success', 'পদবীটি সফলভাবে মুছে ফেলা হয়েছে।');
    }

    /**
     * AJAX endpoint to get designations for a department.
     */
    public function ajaxGetDesignations($departmentId)
    {
        $department = Department::where('user_id', Auth::id())->findOrFail($departmentId);
        $designations = Designation::where('department_id', $department->id)->get(['id', 'name']);

        return response()->json($designations);
    }
}
