<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Building;
use App\Models\Licence;
use App\Models\Branch;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class BuildingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $licenceno = Auth::user()->licence_no;
        $branchid = Auth::user()->branch_id;

        $building = Building::with([
            'licence:id,licence_no',
            'branch:id,branch_name,b_city'
        ])
        ->where('licence_no', $licenceno)
        ->where('branch_id', $branchid)
        ->get();

        return response()->json([
            'status' => true,
            'data' => $building
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'licence_no' => 'nullable|exists:licences,licence_no',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'building' => 'required|string|unique:buildings,building',
            'other1' => 'nullable|string|max:255',
            'other2' => 'nullable|string|max:255',
            'other3' => 'nullable|string|max:255',
            'other4' => 'nullable|string|max:255',
            'other5' => 'nullable|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);
        }

         $licence = Licence::where('licence_no', $request->licence_no)->first();
            if (!$licence) {
                return response()->json(['error' => 'Invalid licence number.'], 404);
            }

            // Step 2: Branch check against this licence
            $branch = Branch::where('id', $request->branch_id)
                            ->where('licence_no', $request->licence_no)
                            ->first();

            if (!$branch) {
                return response()->json([
                    'error' => 'The selected branch does not belong to the provided licence_no.'
                ], 422);
            }

            try{
                $building = Building::create([
                    'licence_no' => $request->licence_no,
                    'branch_id' => $request->branch_id,
                    'building' => $request->building,
                    'other1' => $request->other1,
                    'other2' => $request->other2,
                    'other3' => $request->other3,
                    'other4' => $request->other4,
                    'other5' => $request->other5,
                ]);

                 $building = $building->load(['licence', 'branch']);

            return response()->json([
                'status' => true,
                'message' => 'building added successfully',
                'data' => $building
            ], 200);

            } catch (\Exception $e) {
                return response()->json(['message' => 'Something went wrong', 'error' => $e->getmessage()],500);

            }
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'licence_no' => 'nullable|exists:licences,licence_no',
            'branch_id' => 'nullable|exists:branches,id',
            'building' => 'required',
            'other1' => 'nullable|string|max:255',
            'other2' => 'nullable|string|max:255',
            'other3' => 'nullable|string|max:255',
            'other4' => 'nullable|string|max:255',
            'other5' => 'nullable|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);
        }

        $licence = Licence::where('licence_no', $request->licence_no)->first();
            if (!$licence) {
                return response()->json(['error' => 'Invalid licence number.'], 404);
            }

            // Step 2: Branch check against this licence
            $branch = Branch::where('id', $request->branch_id)
                            ->where('licence_no', $request->licence_no)
                            ->first();

            if (!$branch) {
                return response()->json([
                    'error' => 'The selected branch does not belong to the provided licence_no.'
                ], 422);
            }

            try{
                $building = Building::findOrFail($id);

                $building->update([
                'licence_no' => $request->licence_no,
                'branch_id' => $request->branch_id,
                'building' => $request->building,
                'other1' => $request->other1,
                'other2' => $request->other2,
                'other3' => $request->other3,
                'other4' => $request->other4,
                'other5' => $request->other5,
                ]);


                $building = $building->load(['licence', 'branch']);

                return response()->json([
                    'status' => true,
                    'message' => 'building updated successfully',
                    'data' => $building
                ], 200);
            } catch (\Exception $e)
                 {
                    return response()->json([
                        'success' => false,
                        'message' => 'somthing went wrong',
                        'error' => $e->getmessage()
                    ],500);
                    }


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $building = Building::findorFail($id);

            $building->delete();

            return response()->json(['status' => true, 'message' => 'building  deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mesage' => 'somthing went wrong',
                'error' => $e->getmessage()
            ],500);
        }
    }
}
