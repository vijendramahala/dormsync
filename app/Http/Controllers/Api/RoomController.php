<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Licence;
use App\Models\Branch;
use App\Models\Building;
use App\Models\Floor;
use Illuminate\Support\Facades\Validator;


class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rooms = Room::with(['licence', 'branch','building','floor'])->get();

     return response()->json([
        'status' => true,
        'data' => $rooms
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
             'licence_no'  => 'required|exists:licences,licence_no',
            'branch_id'   => 'required|exists:branches,id',
            'building_id' => 'required|exists:buildings,id',
            'floor_id'     => 'required|exists:floors,id',  
            'room_no'     => 'required|string|max:100',
            'room_type'   => 'nullable|in:AC,Non-AC',
            'room_beds'   => 'required|integer|min:1',
            'occupancy_status' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()], 422);
        }
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
        $floor = Floor::where('id', $request->floor_id)
                            ->where('building_id', $request->building_id)
                            ->first();
        if (!$floor) {
            return response()->json([
                'error' => 'The selected floor does not belong to the provided building_id.'
            ], 422);
        }

        try{
            $rooms = Room::create([
                'licence_no' => $request->licence_no,
                'branch_id' => $request->branch_id,
                'building_id' => $request->building_id,
                'floor_id' => $request->floor_id,
                'room_no' => $request->room_no,
                'room_type' => $request->room_type,
                'room_beds' => $request->room_beds,
                'occupancy_status' => $request->occupancy_status
            ]);
                 $rooms = $rooms->load(['licence', 'branch','building','floor']);

                 return response()->json([
                'message' => 'room added successfully',
                'data' => $rooms
            ], 201);

        } catch (\Exception $e){
            return response()->json([
                'error' => 'Something went wrong',
                'message' => $e->getmessage()
            ],500);
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
             'licence_no'  => 'required|exists:licences,licence_no',
            'branch_id'   => 'required|exists:branches,id',
            'building_id' => 'required|exists:buildings,id',
            'floor_id'     => 'required|exists:floors,id',  
            'room_no'     => 'required|string|max:100',
            'room_type'   => 'nullable|in:AC,Non-AC',
            'room_beds'   => 'required|integer|min:1',
            'occupancy_status' => 'required'

        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()], 422);
        }
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
        $floor = Floor::where('id', $request->floor_id)
                            ->where('building_id', $request->building_id)
                            ->first();
        if (!$floor) {
            return response()->json([
                'error' => 'The selected floor does not belong to the provided building_id.'
            ], 422);
        }
        try{
            $rooms = Room::findorFail($id);

            $rooms->update([
                'licence_no' => $request->licence_no,
                'branch_id' => $request->branch_id,
                'building_id' => $request->building_id,
                'floor_id' => $request->floor_id,
                'room_no' => $request->room_no,
                'room_type' => $request->room_type,
                'room_beds' => $request->room_beds,
                'occupancy_status' => $request->occupancy_status
            ]);

            $rooms = $rooms->load(['licence', 'branch','building','floor']);

            return response()->json([
                    'success' => true,
                    'message' => 'rooms updated successfully',
                    'data' => $rooms
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
            $rooms = Room::findorFail($id);

            $rooms->delete();

            return response()->json(['message' => 'rooms deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mesage' => 'somthing went wrong',
                'error' => $e->getmessage()
            ],500);
        }
    }
}
