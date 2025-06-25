<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Roomassign;
use App\Models\Room;
use App\Models\Admissionform;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RoomassignController extends Controller
{
    public function index()
    {
        $licenceno = Auth::user()->licence_no;
        $branchid = Auth::user()->branch_id;

        $room = Roomassign::with([
            'licence:id,licence_no',
            'branch:id,branch_name,b_city'
        ])
        ->where('licence_no', $licenceno)
        ->where('branch_id', $branchid)
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Successfully fetched',
            'data' => $room
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'licence_no'       => 'required|exists:licences,licence_no',
            'branch_id'        => 'required|exists:branches,id',
            'hosteler_details' => 'nullable|string|max:1000',
            'hosteler_id'      => 'required|exists:admissionforms,student_id',
            'admission_date'   => 'required|date',
            'hosteler_name'    => 'required|string|max:255',
            'course_name'      => 'nullable|string|max:255',
            'father_name'      => 'required|string|max:255',
            'building_id'      => 'required|exists:buildings,id',
            'floor_id'         => 'required|exists:floors,id',
            'room_type'        => 'required|in:A/C,Non-A/C',
            'room_no'          => 'required|string|max:50',
            // 'active_status' => 'required',
            'other1' => 'nullable|string|max:255',
            'other2' => 'nullable|string|max:255',
            'other3' => 'nullable|string|max:255',
            'other4' => 'nullable|string|max:255',
            'other5' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);
        }

$admissionform = Admissionform::where('student_id', $request->hosteler_id)->first();

        $room = Room::where([
            'licence_no'   => $request->licence_no,
            'branch_id'    => $request->branch_id,
            'building_id'  => $request->building_id,
            'floor_id'     => $request->floor_id,
            'room_type'    => $request->room_type,
            'room_no'      => $request->room_no,
        ])->first();

        if (!$room) {
            return response()->json(['status' => false, 'message' => 'Room not found'], 200);
        }

        $hostelers = $room->hosteler_id ? json_decode($room->hosteler_id, true) : [];
        if (!is_array($hostelers)) {
            $hostelers = [];
        }

        if (in_array($request->hosteler_id, $hostelers)) {
            return response()->json(['status' => false, 'message' => 'This student is already assigned to this room.'], 200);
        }

        if ($room->current_occupants >= $room->room_beds) {
            return response()->json(['status' => false, 'message' => 'Room is already full'], 200);
        }

        try {
            $this->removeHostelerFromOtherRooms($request->hosteler_id);

            $roomassign = Roomassign::create([
        'licence_no'       => $request->licence_no,
        'branch_id'        => $request->branch_id,
        'hosteler_details' => $request->hosteler_details,
        'hosteler_id'      => $request->hosteler_id,
        'admission_date'   => $request->admission_date,
        'hosteler_name'    => $request->hosteler_name,
        'course_name'      => $request->course_name,
        'father_name'      => $request->father_name,
        'building_id'      => $request->building_id,
        'floor_id'         => $request->floor_id,
        'room_type'        => $request->room_type,
        'room_no'          => $request->room_no,
        'room_id'          => $room->id,
        'active_status' => $admissionform->active_status,
        'other1' => $request->other1,
        'other2' => $request->other2,
        'other3' => $request->other3,
        'other4' => $request->other4,
        'other5' => $request->other5,
    ]);


            $hostelers[] = $request->hosteler_id;
            $room->hosteler_id = json_encode(array_unique($hostelers));
            $room->current_occupants = count($hostelers);
            $left = $room->room_beds - $room->current_occupants;
            $room->occupancy_status = $left <= 0 ? 'Full' : ($left == $room->room_beds ? 'Empty' : 'Left');
            $room->save();

            return response()->json([
                'status' => true,
                'message' => 'Room assigned successfully',
                'data' => $roomassign,
                'room_status' => [
                    'current_occupants' => $room->current_occupants,
                    'occupancy_status' => $room->occupancy_status,
                    'hosteler_id' => json_decode($room->hosteler_id)
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'licence_no'        => 'required|exists:licences,licence_no',
            'branch_id'         => 'required|exists:branches,id',
            'hosteler_details'  => 'nullable|string|max:1000',
            'hosteler_id'       => 'required|exists:admissionforms,student_id',
            'admission_date'    => 'required|date',
            'hosteler_name'     => 'required|string|max:255',
            'course_name'       => 'nullable|string|max:255',
            'father_name'       => 'required|string|max:255',
            'building_id'       => 'required|exists:buildings,id',
            'floor_id'          => 'required|exists:floors,id',
            'room_type'         => 'required|in:A/C,Non-A/C',
            'room_no'           => 'required|string|max:50',
            'other1' => 'nullable|string|max:255',
            'other2' => 'nullable|string|max:255',
            'other3' => 'nullable|string|max:255',
            'other4' => 'nullable|string|max:255',
            'other5' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);
        }

        try {
            $assign = Roomassign::findOrFail($id);

            // Check if same student already assigned to same room
            if (
                $assign->hosteler_id === $request->hosteler_id &&
                $assign->room_no === $request->room_no &&
                $assign->building_id === (int)$request->building_id &&
                $assign->room_type === $request->room_type
            ) {
                return response()->json(['status' => false, 'message' => 'This student is already assigned to this room'], 200);
            }

            $newRoom = Room::where([
                'licence_no' => $request->licence_no,
                'branch_id' => $request->branch_id,
                'building_id' => $request->building_id,
                'floor_id' => $request->floor_id,
                'room_no' => $request->room_no,
                'room_type' => $request->room_type,
            ])->first();

            if (!$newRoom) {
                return response()->json(['status' => false, 'message' => 'Room not found.'], 200);
            }

            // Check if room is already full
            $hostelerList = json_decode($newRoom->hosteler_id ?? '[]', true);
            if (!is_array($hostelerList)) $hostelerList = [];

            if (
                in_array($request->hosteler_id, $hostelerList) === false &&
                count($hostelerList) >= $newRoom->room_beds
            ) {
                return response()->json(['status' => false, 'message' => 'Room is already full'], 200);
            }

            // Remove from previous room
            $this->removeHostelerFromOtherRooms($request->hosteler_id);

            // Add hosteler to new room
            $hostelerList[] = $request->hosteler_id;
            $hostelerList = array_unique($hostelerList);

            $newRoom->hosteler_id = json_encode($hostelerList);
            $newRoom->current_occupants = count($hostelerList);
            $left = $newRoom->room_beds - $newRoom->current_occupants;
            $newRoom->occupancy_status = $left <= 0 ? 'Full' : ($left == $newRoom->room_beds ? 'Empty' : 'Left');
            $newRoom->save();

            // Update Roomassign entry
            $assign->update([
        'licence_no'       => $request->licence_no,
        'branch_id'        => $request->branch_id,
        'hosteler_details' => $request->hosteler_details,
        'hosteler_id'      => $request->hosteler_id,
        'admission_date'   => $request->admission_date,
        'hosteler_name'    => $request->hosteler_name,
        'course_name'      => $request->course_name,
        'father_name'      => $request->father_name,
        'building_id'      => $request->building_id,
        'floor_id'         => $request->floor_id,
        'room_type'        => $request->room_type,
        'room_no'          => $request->room_no,
        'room_id'          => $newRoom->id,
        'other1' => $request->other1,
        'other2' => $request->other2,
        'other3' => $request->other3,
        'other4' => $request->other4,
        'other5' => $request->other5,
    ]);


            return response()->json([
                'status' => true,
                'message' => 'Room assignment updated successfully',
                'data' => $assign
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    private function removeHostelerFromOtherRooms($hostelerId)
    {
        $rooms = Room::whereJsonContains('hosteler_id', $hostelerId)->get();

        foreach ($rooms as $room) {
            $hostelers = is_array($room->hosteler_id) ? $room->hosteler_id : json_decode($room->hosteler_id, true);
            if (!is_array($hostelers)) continue;

            $updated = array_values(array_filter($hostelers, fn($id) => $id != $hostelerId));
            $room->hosteler_id = json_encode($updated);
            $room->current_occupants = count($updated);
            $left = $room->room_beds - $room->current_occupants;
            $room->occupancy_status = $left <= 0 ? 'Full' : ($left == $room->room_beds ? 'Empty' : 'Left');
            $room->save();
        }
    }

    public function destroy(string $id)
    {
        try {
            $assign = Roomassign::findOrFail($id);
            $hostelerId = $assign->hosteler_id;
            $admissionform = Admissionform::where('student_id', $hostelerId)->first();

            if($admissionform){
                    $admissionform->update([
                        'active_status' => 1
                    ]);
            }

            // Find the room the student was assigned to
            $room = Room::where([
                'licence_no' => $assign->licence_no,
                'branch_id' => $assign->branch_id,
                'building_id' => $assign->building_id,
                'floor_id' => $assign->floor_id,
                'room_no' => $assign->room_no,
                'room_type' => $assign->room_type,
            ])->first();

            if ($room) {
                $hostelers = json_decode($room->hosteler_id ?? '[]', true);
                if (!is_array($hostelers)) $hostelers = [];

                // Remove hosteler ID
                $updated = array_values(array_filter($hostelers, fn($id) => $id != $hostelerId));

                // Update Room
                $room->hosteler_id = json_encode($updated);
                $room->current_occupants = count($updated);
                $left = $room->room_beds - $room->current_occupants;
                $room->occupancy_status = $left <= 0 ? 'Full' : ($left == $room->room_beds ? 'Empty' : 'Left');
                $room->save();
            }

            // Delete room assignment
            $assign->delete();

            return response()->json([
                'status' => true,
                'message' => 'Room assignment deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkRoomAssignment($hostelerId)
    {
        try {
            $userLicence = Auth::user()->licence_no;
            $userBranch = Auth::user()->branch_id;

            $assignment = Roomassign::where('hosteler_id', $hostelerId)
                ->where('licence_no', $userLicence)
                ->where('branch_id', $userBranch)
                ->first();

            if ($assignment) {
                return response()->json([
                    'status' => true,
                    'message' => 'Hosteler is assigned to a room.',
                    'data' => [
                        'hosteler_id' => $assignment->hosteler_id,
                        'hosteler_name' => $assignment->hosteler_name,
                        'room_no' => $assignment->room_no,
                        'room_type' => $assignment->room_type,
                        'building_id' => $assignment->building_id,
                        'floor_id' => $assignment->floor_id,
                        'room_id' => $assignment->room_id,
                        'id' => $assignment->id,
                    ]
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Hosteler is not assigned to any room.'
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }


   public function admissionroomassign(Request $request)
    {
        $licenceno = Auth::user()->licence_no;
        $branchid = Auth::user()->branch_id;

        $roomAssignments = Roomassign::where('licence_no', $licenceno)
            ->where('branch_id', $branchid)
            ->get();

        $admissionQuery = Admissionform::where('licence_no', $licenceno)
            ->where('branch_id', $branchid);

        if ($request->filled('from_date')) {
            $fromDate = Carbon::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $admissionQuery->whereRaw("STR_TO_DATE(admission_date, '%d/%m/%Y') >= ?", [$fromDate]);
        }

        if ($request->filled('to_date')) {
            $toDate = Carbon::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $admissionQuery->whereRaw("STR_TO_DATE(admission_date, '%d/%m/%Y') <= ?", [$toDate]);
        }

        $admissions = $admissionQuery->get()->keyBy('student_id');

        $final = [];

        foreach ($roomAssignments as $assign) {
            $studentId = $assign->hosteler_id;

            if (isset($admissions[$studentId])) {
                $admission = $admissions[$studentId];

                $final[] = [
                    'student_id' => $admission->student_id,
                    'student_name' => $admission->student_name,
                    'father_name' => $admission->father_name,
                    'primary_contact_no' => $admission->primary_contact_no,
                    'admission_date' => $admission->admission_date,
                    'room_no' => $assign->room_no,
                    'room_type' => $assign->room_type,
                    'building_id' => $assign->building_id,
                    'floor_id' => $assign->floor_id,
                    'room_id' => $assign->room_id,
                    'id' => $assign->id
                ];
            }
        }

        return response()->json([
            'status' => true,
            'data' => $final
        ], 200);
    }


}