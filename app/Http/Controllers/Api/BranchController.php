<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Licence;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $licenceNo = $request->input('licence_no');

        $branches = Branch::with(['user' => function ($query) {
                $query->select('id', 'branch_id', 'username', 'password')
                    ->where('role', 'admin');
            }])
            ->where('licence_no', $licenceNo)
            ->get();

        return response()->json([
            'status' => true,
            'branches' => $branches
        ], 200);
    }




    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:branches',
                'branch_name' => 'required',
                'b_address' => 'required',
                'b_city' => 'required',
                'b_state' => 'required',
                'licence_id' => 'nullable|exists:licences,id',
                'licence_no' => 'required',
                'contact_no' => 'required',
                // 'u_name' => 'required',
                // 'username' => 'required|unique:users,username',
                'password' => 'required|min:6',
            ]);
            
            if($validator->fails()){
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 200);
            }

            $branch = Branch::create([
                'name' => $request->name,
                'branch_name' => $request->branch_name,
                'b_address' => $request->b_address,
                'b_city' => $request->b_city,
                'b_state' => $request->b_state,
                'licence_id' => $request->licence_id,
                'licence_no' => $request->licence_no,
                'contact_no' => $request->contact_no,
            ]);

            $branch->update([
                'location_id' => $branch->id
            ]);

            User::create([
                'branch_id' => $branch->id,
                'licence_no' => $branch->licence_no,
                'u_name' => $branch->name,
                'username' => $branch->name,
                'password' => $request->password,
                'role' => 'admin',
            ]);

            $branch = $branch->load(['licence']);
            $licence = Licence::where('licence_no', $branch->licence_no)->first();

            return response()->json([
                'status' => true,
                'message' => 'Branch created successfully.',
                'branch' => $branch,
                'licence_by_no' => $licence
            ], 200);  
              } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        $branch = Branch::with(['users' => function ($query) {
            $query->whereIn('role', ['admin', 'subadmin']);
        }])->find($id);

        if (!$branch) {
            return response()->json(['success' => false, 'message' => 'Branch not found'], 404);
        }

        return response()->json(['success' => true, 'branch' => $branch]);
    }

    public function update(Request $request, string $id)
    {
        $branch = Branch::findOrFail($id);

        $user = User::where('branch_id', $branch->id)->where('role', 'admin')->first();

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:branches,name,' . $id,
            'branch_name' => 'nullable',
            'b_address' => 'required',
            'b_city' => 'required',
            'b_state' => 'required',
            'licence_id' => 'nullable|exists:licences,id',
            'location_id' => 'nullable',
            'username' => 'nullable|string|unique:users,username,' . ($user->id ?? 'null'),
            'password' => 'nullable|min:6',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ],200);
    }

        $branch->update([
            'name' => $request->name,
            'contact_no' => $request->contact_no,
            'branch_name' => $request->branch_name,
            'b_address' => $request->b_address,
            'b_city' => $request->b_city,
            'b_state' => $request->b_state,
            'licence_no' => $request->licence_no,
            'location_id' => $request->location_id,
        ]);

        $branch->update(['location_id' => $branch->id]);

        if ($user) {
            $user->u_name = $branch->name;
            if ($request->filled('username')) {
                $user->username = $request->username;
            }
            if ($request->filled('password')) {
                $user->password = $request->password;
            }
            $user->licence_no = $branch->licence_no;
            $user->save();

            $branch = $branch->load(['licence']);
            $licence = Licence::where('licence_no', $branch->licence_no)->first();

                    return response()->json([
                    'status' => true,
                    'message' => 'Branch update successfully.',
                    'branch' => $branch,
                    'licence_by_no' => $licence
                ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'No admin user found for this branch to update.'
        ], 404);
    }

    public function destroy(string $id)
    {
        try {
            $branch = Branch::findOrFail($id);

            if ($branch->users) {
                $branch->users()->delete();
            }

            $branch->delete();

            return response()->json(['success' => true, 'message' => 'Branch and its users deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting branch: ' . $e->getMessage()], 500);
        }
    }

    public function getBranchesByLicence(Request $request)
{
    $request->validate([
        'licence_no' => 'required|string',
    ]);

    $branches = Branch::where('licence_no', $request->licence_no)->get();

    if ($branches->isEmpty()) {
        return response()->json([
            'status' => false,
            'message' => 'No branches found for this licence number',
        ], 200);
    }

    return response()->json([
        'status' => true,
        'message' => 'Branch sucessfully Fatch',
        'branches' => $branches
    ], 200);
}

    public function superadmin(Request $request)
    {
        $request->validate([
            // 'branch_id' => 'required|exists:branches,id',
            'u_name' => 'required|string',
            'username' => 'required|unique:users,username',
            'password' => 'required|min:6',
        ]);

        // $branch = Branch::find($request->branch_id);

        $user = User::create([
            'u_name' => $request->u_name,
            'username' => $request->username,
            'password' => $request->password,
            'role' => 'superadmin',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User added  successfully',
            'user' => $user
        ], 201);
    }
    public function changePassword(Request $request)
    {
        $request->validate([
            'licence_no' => 'required|string',
            'branch_id' => 'required|integer',
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6',
            'confirm_password' => 'required|same:new_password',
        ]);

        // Get the admin user
        $user = User::where('licence_no', $request->licence_no)
                    ->where('branch_id', $request->branch_id)
                    ->where('role', 'admin')
                    ->first();


        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Check old password
        if ($request->old_password !== $user->password) {
            return response()->json(['error' => 'Old password incorrect'], 400);
        }

        // Update new password
        $user->password = $request->new_password;
        $user->save();

        return response()->json(['message' => 'Password changed successfully']);
    }
}
