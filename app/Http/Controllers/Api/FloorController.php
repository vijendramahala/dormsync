<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Floor;
use App\Models\Licence;
use App\Models\Branch;
use App\Models\Building;
use Illuminate\Support\Facades\Validator;

class FloorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $floor = Floor::with(['licence', 'branch','building'])->get();

     return response()->json([
        'status' => true,
        'data' => $floor
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
        // dd($request->all());
        

        $validator = Validator::make($request->all(), [
            'licence_no' => 'nullable|exists:licences,licence_no',
            'branch_id' => 'nullable|exists:branches,id',
            'building_id' => 'nullable|exists:buildings,id',
            'floor' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()], 422);
        }
      // Step 1: Check if licence exists
        $licence = Licence::where('licence_no', $request->licence_no)->first();
        if (!$licence) {
            return response()->json(['error' => 'Invalid licence number.'], 404);
        }

        // Step 2: Check if branch belongs to this licence
        $branch = Branch::where('id', $request->branch_id)
                        ->where('licence_no', $request->licence_no)
                        ->first();
        if (!$branch) {
            return response()->json([
                'error' => 'The selected branch does not belong to the provided licence_no.'
            ], 422);
        }

        // Step 3: Check if building belongs to this branch
        $building = Building::where('id', $request->building_id)
                            ->where('branch_id', $request->branch_id)
                            ->first();
        if (!$building) {
            return response()->json([
                'error' => 'The selected building does not belong to the provided branch_id.'
            ], 422);
        }


            try {
                $floor = Floor::create([
                    'licence_no' => $request->licence_no,
                    'branch_id' => $request->branch_id,
                    'building_id' => $request->building_id,
                    'floor' => $request->floor
                ]);

                $floor = $floor->load(['licence', 'branch','building']);

            return response()->json([
                'message' => 'floor added successfully',
                'data' => $floor
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
            'building_id' => 'nullable|exists:buildings,id',
            'floor' => 'required'
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

            $building = Building::where('id', $request->building_id)->first();
            if (!$building) {
                return response()->json(['error' => 'Invalid building number.'], 404);
            }
            try {
                $floor = Floor::findorFail($id);

                $floor->update([
                    'licence_no' => $request->licence_no,
                    'branch_id' => $request->branch_id,
                    'building_id' => $request->building_id,
                    'floor' => $request->floor
                ]);

                $floor = $floor->load(['licence', 'branch','building']);

                return response()->json([
                    'success' => true,
                    'message' => 'floor updated successfully',
                    'data' => $floor
                ]);
            }catch (\Exception $e)
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
            $floor = Floor::findorFail($id);

            $floor->delete();

            return response()->json(['message' => 'floor deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mesage' => 'somthing went wrong',
                'error' => $e->getmessage()
            ],500);
        }
    }
}
