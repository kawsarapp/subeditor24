<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentDesignationController extends Controller
{
    /**
     * Get effective admin user and authorize can_manage_staff permission.
     */
    private function getEffectiveAdmin()
    {
        $user = Auth::user();
        if ($user->role !== 'super_admin' && !$user->hasPermission('can_manage_staff')) {
            abort(403, 'দুঃখিত, আপনার কাছে এই সেকশন ম্যানেজ করার অনুমতি নেই।');
        }
        return in_array($user->role, ['staff', 'reporter']) ? ($user->parent ?: $user) : $user;
    }

    /**
     * Store a newly created department.
     */
    public function storeDepartment(Request $request)
    {
        $admin = $this->getEffectiveAdmin();

        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        Department::create([
            'name' => strip_tags($request->name),
            'user_id' => $admin->id,
        ]);

        return back()->with('success', 'নতুন বিভাগ সফলভাবে তৈরি হয়েছে।');
    }

    /**
     * Remove the specified department.
     */
    public function destroyDepartment($id)
    {
        $admin = $this->getEffectiveAdmin();

        $department = Department::where('user_id', $admin->id)->findOrFail($id);
        $department->delete();

        return back()->with('success', 'বিভাগটি সফলভাবে মুছে ফেলা হয়েছে।');
    }

    /**
     * Store a newly created designation.
     */
    public function storeDesignation(Request $request)
    {
        $admin = $this->getEffectiveAdmin();

        $request->validate([
            'name' => 'required|string|max:100',
            'department_id' => 'required|exists:departments,id',
        ]);

        // Verify the department belongs to the logged-in admin
        $department = Department::where('user_id', $admin->id)->findOrFail($request->department_id);

        Designation::create([
            'name' => strip_tags($request->name),
            'department_id' => $department->id,
            'user_id' => $admin->id,
        ]);

        return back()->with('success', 'নতুন পদবী সফলভাবে তৈরি হয়েছে।');
    }

    /**
     * Remove the specified designation.
     */
    public function destroyDesignation($id)
    {
        $admin = $this->getEffectiveAdmin();

        $designation = Designation::where('user_id', $admin->id)->findOrFail($id);
        $designation->delete();

        return back()->with('success', 'পদবীটি সফলভাবে মুছে ফেলা হয়েছে।');
    }

    /**
     * AJAX endpoint to get designations for a department.
     */
    public function ajaxGetDesignations($departmentId)
    {
        $admin = $this->getEffectiveAdmin();

        $department = Department::where('user_id', $admin->id)->findOrFail($departmentId);
        $designations = Designation::where('department_id', $department->id)->get(['id', 'name']);

        return response()->json($designations);
    }
}
