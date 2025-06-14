<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Licence;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class LicenceController extends Controller
{
    public function index()
    {
        $licenses = Licence::with(['user:licence_no,username,password'])->get();

        return response()->json([
            'status' => true,
            'data' => $licenses
        ]);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);
        }

        $validator = Validator::make($request->all(), [
            'licence_no' => 'required|unique:licences,licence_no',
            'license_due_date' => 'required|date',
            'amc_due_date' => 'required|date',
            'company_name' => 'required',
            'l_address' => 'required',
            'l_city' => 'required',
            'l_state' => 'required',
            'gst_no' => 'nullable',
            'owner_name' => 'required|unique:licences,owner_name',
            'contact_no' => 'required',
            'deal_amt' => 'nullable|numeric',
            'receive_amt' => 'nullable|numeric',
            'due_amt' => 'nullable|numeric',
            'branch_count' => 'required|integer',
            'remarks' => 'nullable',
            'branch_name' => 'required|unique:branches,branch_name',
            'username' => 'required|unique:users,username',
            'password' => 'required|min:6',
            // 'location_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);
        }

        try {
            $license = Licence::create($request->only([
                'licence_no',
                'license_due_date',
                'amc_due_date',
                'company_name',
                'l_address',
                'l_city',
                'l_state',
                'gst_no',
                'owner_name',
                'contact_no',
                'deal_amt',
                'receive_amt',
                'due_amt',
                'branch_count',
                'remarks',
                'salesman'
            ]));

            // Create main branch
            $branch = Branch::create([
                'name' => $license->owner_name,
                'licence_no' => $license->licence_no,
                'contact_no' => $license->contact_no,
                'branch_name' => $license->company_name,
                'b_address' => $license->l_address,
                'b_city' => $license->l_city,
                'b_state' => $license->l_state,
                'location_id' => $request->location_id
            ]);
            $branch->update([
                'location_id' => $branch->id
            ]);

            // Create user
            $user = User::create([
                'licence_no' => $license->licence_no,
                'branch_id' => $branch->id,
                'u_name' => $license->owner_name,
                'username' => $request->username,
                'password' => $request->password,
                'role' => 'admin'
            ]);

            // Generate dummy branches
            $branchesData = [];

        if ($request->has('branch_list') && is_array($request->branch_list)) {
            // Use user-supplied branch list if available
            $branchesData = $request->branch_list;
        } else {
            // Fallback to dummy branches
            for ($i = 1; $i <= $license->branch_count; $i++) {
                $branchesData[] = [
                    'branch_name' => 'Branch ' . $i,
                    'b_address' => 'Address ' . $i,
                    'b_city' => 'City ' . $i,
                    'b_state' => 'State ' . $i,
                ];
            }
        }

        $license->update([
            'branch_list' => json_encode($branchesData)
        ]);


            $license->update([
                'branch_list' => json_encode($branchesData)
            ]);

            return response()->json([
                'status' => true,
                'message' => 'License created successfully with branch list',
                'license_id' => $license->id
            ], 200);

        } catch (\Exception $e) {
            \Log::error('License creation error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $license = Licence::findOrFail($id);

            $existingBranch = Branch::where('licence_no', $license->licence_no)->first();

            $validator = Validator::make($request->all(), [
                'license_due_date' => 'required|date',
                'amc_due_date' => 'required|date',
                'company_name' => 'required',
                'l_address' => 'required',
                'l_city' => 'required',
                'l_state' => 'required',
                'gst_no' => 'nullable',
                'owner_name' => 'required',
                'contact_no' => 'required',
                'deal_amt' => 'nullable|numeric',
                'receive_amt' => 'nullable|numeric',
                'due_amt' => 'nullable|numeric',
                'branch_count' => 'required|integer',
                'remarks' => 'nullable',
                'branch_name' => 'required',
                'name' => 'required', // unique validation hata diya
                'username' => 'required',
                'password' => 'required|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);
            }

            // Update license data
            $license->update($request->only([
                'license_due_date',
                'amc_due_date',
                'company_name',
                'l_address',
                'l_city',
                'l_state',
                'gst_no',
                'owner_name',
                'contact_no',
                'deal_amt',
                'receive_amt',
                'due_amt',
                'branch_count',
                'remarks'
            ]));

            // Delete old branches
            Branch::where('licence_no', $license->licence_no)->delete();

            // Recreate main branch
            $branch = Branch::create([
                'name' => $license->owner_name,
                'licence_no' => $license->licence_no,
                'contact_no' => $license->contact_no,
                'branch_name' => $license->company_name,
                'b_address' => $license->l_address,
                'b_city' => $license->l_city,
                'b_state' => $license->l_state,
                'location_id' => $request->location_id,
            ]);
            $branch->update(['location_id' => $branch->id]);

            // Update or create user
            $user = User::where('licence_no', $license->licence_no)->first();

            if ($user) {
                $user->update([
                    'u_name' => $license->owner_name,
                    'username' => $request->username,
                    'password' => $request->password, // Hash if needed
                    'role' => 'admin'
                ]);
            } else {
                User::create([
                    'licence_no' => $license->licence_no,
                    'branch_id' => $branch->id,
                    'u_name' => $license->owner_name,
                    'username' => $request->username,
                    'password' => $request->password, // Hash if needed
                    'role' => 'admin'
                ]);
            }

            // If frontend sends branch_list, use that
            if ($request->has('branch_list') && is_array($request->branch_list)) {
                $license->update([
                    'branch_list' => json_encode($request->branch_list)
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'License updated successfully',
                'license_id' => $license->id
            ], 200);

        } catch (\Exception $e) {
            \Log::error('License update error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $license = Licence::findOrFail($id);

            // Delete associated branches
            Branch::where('licence_no', $license->licence_no)->delete();

            // Delete associated users
            User::where('licence_no', $license->licence_no)->delete();

            // Delete the license itself
            $license->delete();

            return response()->json([
                'status' => true,
                'message' => 'License and related data deleted successfully.'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('License deletion error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }




}
