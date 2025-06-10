<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Licence;
use \App\Models\Branch;

class AuthController extends Controller
{

    public function superAdminLogin(Request $request)
    {
        // Step 1: Validate basic fields
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Step 2: Find user by username
        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Step 3: Check role
        if (!in_array($user->role, ['admin', 'superadmin', 'subadmin', 'staff'])) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized user',
            ], 403);
        }

        // ✅ IF ROLE IS SUPERADMIN — SKIP LICENCE/BRANCH VALIDATION
        if ($user->role === 'superadmin') {
            $token = $user->createToken('superadmin-token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Login successful (Superadmin)',
                'token' => $token,
                'user' => $user,
                'license' => null,
                'branch' => null,
            ], 200);
        }

        // ✅ OTHER ROLES — Require licence_no and branch_name
        $request->validate([
            'licence_no' => 'required|string',
            'branch_name' => 'required|string',
        ]);

        // Step 4: Get branch
        $branch = Branch::where('licence_no', $request->licence_no)
                        ->where('branch_name', $request->branch_name)
                        ->first();

        if (!$branch) {
            return response()->json([
                'status' => false,
                'message' => 'Branch not found for this license number and branch name.',
            ], 404);
        }

        // Step 5: Confirm user is from this branch
        if ($user->licence_no !== $request->licence_no || $user->branch_id !== $branch->id) {
            return response()->json([
                'status' => false,
                'message' => 'User does not belong to this branch or license.',
            ], 403);
        }

        // Step 6: License check
        $licenseData = null;
        $license = Licence::where('licence_no', $request->licence_no)->first();

        if ($license) {
            $licenseDueDate = $license->license_due_date;
            $amcDueDate = $license->amc_due_date;
            $currentDate = now();

            if ($licenseDueDate && $currentDate > $licenseDueDate) {
                return response()->json([
                    'status' => false,
                    'message' => 'License has expired',
                ], 403);
            }
            
            $licenseData = [
                'licence_no' => $license->licence_no,
                'license_due_date' => $licenseDueDate,
                'amc_due_date' => $amcDueDate,
                'company_name' => $license->company_name,
            ];
        }

        // Step 7: Branch data
        $branchData = [
            'branch_id' => $branch->id,
            'branch_name' => $branch->branch_name,
            'address' => $branch->b_address,
            'city' => $branch->b_city,
            'state' => $branch->b_state,
            'contact_no' => $branch->contact_no,
        ];

        // Step 8: Token
        $token = $user->createToken('superadmin-token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
            'license' => $licenseData,
            'branch' => $branchData,
        ], 200);
    }


}
