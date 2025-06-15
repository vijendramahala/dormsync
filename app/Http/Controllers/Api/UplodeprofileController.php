<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Uplodeprofile;
use App\Models\Licence;
use App\Models\Branch;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class UplodeprofileController extends Controller
{
    public function index()
    {
        $licenceno = Auth::user()->licence_no;
        $branchid = Auth::user()->branch_id;

        $profile = Uplodeprofile::with([
            'licence:id,licence_no',
            'branch:id,branch_name,b_city'
        ])
        ->where('licence_no', $licenceno)
        ->where('branch_id', $branchid)
        ->get();

        return response()->json([
            'status' => true,
            'data' => $profile
        ], 200);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'licence_no' => 'required|string|exists:licences,licence_no',
            'branch_id' => 'required|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()], 422);
        }

        // Branch fetch karo and check karo uska licence_no
        $branch = Branch::find($request->branch_id);

        if (!$branch || $branch->licence_no !== $request->licence_no) {
            return response()->json(['error' => 'The selected branch does not belong to the given licence number.'], 422);
        }

        try {
            $profile = Uplodeprofile::create([
                'licence_no' => $request->licence_no,
                'branch_id' => $request->branch_id
            ]);

            if ($request->hasFile('profile') && $request->file('profile')->isValid()) {
            $profile->addMediaFromRequest('profile')->toMediaCollection('profile');
            }

            $profile = $profile->load(['licence', 'branch']);

            return response()->json([
                'message' => 'Profile added successfully',
                'data' => $profile
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'licence_no' => 'required|string|exists:licences,licence_no',
            'branch_id' => 'required|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()], 422);
        }

        $branch = Branch::find($request->branch_id);

        if (!$branch || $branch->licence_no !== $request->licence_no) {
            return response()->json(['error' => 'The selected branch does not belong to the given licence number.'], 422);
        }

        try {
            $profile = Uplodeprofile::findOrFail($id);

            $profile->update([
                'licence_no' => $request->licence_no,
                'branch_id' => $request->branch_id
            ]);

            if ($request->hasFile('profile')) {
            $profile->clearMediaCollection('profile');
            $profile->addMediaFromRequest('profile')->toMediaCollection('profile');
             }

            $profile = $profile->load(['licence', 'branch']);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $profile
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $profile = Uplodeprofile::findorFail($id);

            $profile->clearMediaCollection('profile');

            $profile->delete();

            return response()->json(['message' => 'profile deleted successfully'], 200);

        }  catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete staff', 'message' => $e->getMessage()], 500);
        }
    }
}
