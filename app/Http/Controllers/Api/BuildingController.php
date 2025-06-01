<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Building;
use App\Models\Licence;
use App\Models\Branch;
use Illuminate\Support\Facades\Validator;

class BuildingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $building = Building::with(['licence', 'branch'])->get();

     return response()->json([
        'status' => true,
        'data' => $building
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'licence_no' => 'nullable|exists:licences,licence_no',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'building' => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()], 422);
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
                    'building' => $request->building
                ]);

                 $building = $building->load(['licence', 'branch']);

            return response()->json([
                'message' => 'building added successfully',
                'data' => $building
            ], 201);

            } catch (\Exception $e) {
                return response()->json(['error' => 'Something went wrong', 'message' => $e->getmessage()],500);

            }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
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
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()], 422);
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
                ]);


                $building = $building->load(['licence', 'branch']);

                return response()->json([
                    'success' => true,
                    'message' => 'building updated successfully',
                    'data' => $building
                ]);
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

            return response()->json(['message' => 'building  deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mesage' => 'somthing went wrong',
                'error' => $e->getmessage()
            ],500);
        }
    }
}
