<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Licence;
use App\Models\Branch;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Admissionform;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    public function index()
    {
        $licenceno = Auth::user()->licence_no;
        $branchid = Auth::user()->branch_id;

        $room = Room::with(['licence:id,licence_no', 'branch:id,branch_name,b_city'])
            ->where('licence_no', $licenceno)
            ->where('branch_id', $branchid)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Successfully',
            'data' => $room
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'licence_no'        => 'required|exists:licences,licence_no',
            'branch_id'         => 'required|exists:branches,id',
            'building_id'       => 'required|exists:buildings,id',
            'floor_id'          => 'required|exists:floors,id',
            'room_no'           => 'required|string|unique:rooms,room_no',
            'room_type'         => 'nullable|in:A/C,Non-A/C',
            'room_beds'         => 'required|integer|min:1',
            'current_occupants' => 'nullable|integer|min:0',
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

        $branch = Branch::where('id', $request->branch_id)
                        ->where('licence_no', $request->licence_no)
                        ->first();
        if (!$branch) {
            return response()->json(['error' => 'The selected branch does not belong to the provided licence_no.'], 422);
        }

        $building = Building::where('id', $request->building_id)
                            ->where('branch_id', $request->branch_id)
                            ->first();
        if (!$building) {
            return response()->json(['error' => 'The selected building does not belong to the provided branch_id.'], 422);
        }

        $floor = Floor::where('id', $request->floor_id)
                      ->where('building_id', $request->building_id)
                      ->first();
        if (!$floor) {
            return response()->json(['error' => 'The selected floor does not belong to the provided building_id.'], 422);
        }

        $current = $request->current_occupants ?? 0;
        $beds = $request->room_beds;

        if ($current > $beds) {
            return response()->json(['status' => false, 'message' => 'Occupants exceed total bed capacity.'], 200);
        }

        $status = $current == 0 ? 'Empty' : ($current == $beds ? 'Full' : 'Left');

        try {
            $rooms = Room::create([
                'licence_no'        => $request->licence_no,
                'branch_id'         => $request->branch_id,
                'building_id'       => $request->building_id,
                'floor_id'          => $request->floor_id,
                'room_no'           => $request->room_no,
                'room_type'         => $request->room_type,
                'room_beds'         => $beds,
                'current_occupants' => $current,
                'occupancy_status'  => $status,
                'other1' => $request->other1,
                'other2' => $request->other2,
                'other3' => $request->other3,
                'other4' => $request->other4,
                'other5' => $request->other5,
            ]);

            $rooms = $rooms->load(['licence', 'branch', 'building', 'floor']);

            return response()->json([
                'status' => true,
                'message' => 'Room added successfully',
                'data' => $rooms
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'licence_no'        => 'required|exists:licences,licence_no',
            'branch_id'         => 'required|exists:branches,id',
            'building_id'       => 'required|exists:buildings,id',
            'floor_id'          => 'required|exists:floors,id',
            'room_no'           => 'required|string|max:100',
            'room_type'         => 'nullable|in:A/C,Non-A/C',
            'room_beds'         => 'required|integer|min:1',
            'current_occupants' => 'required|integer|min:0',
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

        $branch = Branch::where('id', $request->branch_id)
                        ->where('licence_no', $request->licence_no)
                        ->first();
        if (!$branch) {
            return response()->json(['error' => 'The selected branch does not belong to the provided licence_no.'], 422);
        }

        $building = Building::where('id', $request->building_id)
                            ->where('branch_id', $request->branch_id)
                            ->first();
        if (!$building) {
            return response()->json(['error' => 'The selected building does not belong to the provided branch_id.'], 422);
        }

        $floor = Floor::where('id', $request->floor_id)
                      ->where('building_id', $request->building_id)
                      ->first();
        if (!$floor) {
            return response()->json(['error' => 'The selected floor does not belong to the provided building_id.'], 422);
        }

        $current = $request->current_occupants;
        $beds = $request->room_beds;

        if ($current > $beds) {
            return response()->json(['status' => false, 'message' => 'Occupants exceed total bed capacity.'], 200);
        }

        $status = $current == 0 ? 'Empty' : ($current == $beds ? 'Full' : 'Left');

        try {
            $rooms = Room::findOrFail($id);

            $rooms->update([
                'licence_no'        => $request->licence_no,
                'branch_id'         => $request->branch_id,
                'building_id'       => $request->building_id,
                'floor_id'          => $request->floor_id,
                'room_no'           => $request->room_no,
                'room_type'         => $request->room_type,
                'room_beds'         => $beds,
                'current_occupants' => $current,
                'occupancy_status'  => $status,
                'other1' => $request->other1,
                'other2' => $request->other2,
                'other3' => $request->other3,
                'other4' => $request->other4,
                'other5' => $request->other5,
            ]);

            $rooms = $rooms->load(['licence', 'branch', 'building', 'floor']);

            return response()->json([
                'status' => true,
                'message' => 'Room updated successfully',
                'data' => $rooms
            ], 200);
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
            $rooms = Room::findOrFail($id);
            $rooms->delete();

            return response()->json(['status' => true, 'message' => 'Room deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    


}