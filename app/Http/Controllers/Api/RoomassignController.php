<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Roomassign;
use App\Models\Licence;
use App\Models\Branch;
use App\Models\Admissionform;
use Illuminate\Support\Facades\Validator;

class RoomassignController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $room = Roomassign::with(['licence', 'branch','student'])->get();

     return response()->json([
        'status' => true,
        'data' => $room
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
        'licence_no'        => 'nullable|exists:licences,licence_no',
        'branch_id'         => 'nullable|exists:branches,id',
        'hosteler_details'  => 'nullable|string|max:1000',
        'hosteler_id'       => 'required',
        'admission_date'    => 'required|date',
        'hosteler_name'     => 'required|string|max:255',
        'course_name'       => 'required|string|max:255',
        'father_name'       => 'required|string|max:255',
        'building'          => 'required|string|max:100',
        'floor'             => 'required|string|max:50',
        'room_type'         => 'required|string|in:AC,Non AC',
        'room_no'           => 'required|string|max:50',
        'room_beds'         => 'required|integer|min:1|max:20',
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
            $admission = Admissionform::where('student_id', $request->hosteler_id)->first();
            if (!$admission) {
                return response()->json(['error' => 'Invalid hosteler id.'], 404);
            }
            try{
                $room = Roomassign::create([
                    'licence_no' => $request->licence_no,
                    'branch_id' => $request->branch_id,
                    'hosteler_details' => $request->hosteler_details,
                    'hosteler_id' => $request->hosteler_id,
                    'admission_date' => $request->admission_date,
                    'hosteler_name' => $request->hosteler_name,
                    'course_name' => $request->course_name,
                    'father_name' => $request->father_name,
                    'building' => $request->building,
                    'floor' => $request->floor,
                    'room_type' => $request->room_type,
                    'room_no' => $request->room_no,
                    'room_beds' => $request->room_beds
                ]);
                $room = $room->load(['licence', 'branch','student']);

                return response()->json([
                'message' => 'Room Assign added successfully',
                'data' => $room
            ], 201);

            } catch (\Exception $e){
                return response()->json([
                    'success' => false,
                    'message' => 'Somthing want wrong',
                    'error' => $e->getmessage()
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
        'licence_no'        => 'nullable|exists:licences,licence_no',
        'branch_id'         => 'nullable|exists:branches,id',
        'hosteler_details'  => 'nullable|string|max:1000',
        'hosteler_id'       => 'required',
        'admission_date'    => 'required|date',
        'hosteler_name'     => 'required|string|max:255',
        'course_name'       => 'required|string|max:255',
        'father_name'       => 'required|string|max:255',
        'building'          => 'required|string|max:100',
        'floor'             => 'required|string|max:50',
        'room_type'         => 'required|string|in:AC,Non AC',
        'room_no'           => 'required|string|max:50',
        'room_beds'         => 'required|integer|min:1|max:20',
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
            $admission = Admissionform::where('student_id', $request->hosteler_id)->first();
            if (!$admission) {
                return response()->json(['error' => 'Invalid hosteler id.'], 404);
            }
            try{
                $room = Roomassign::findorFail($id);

                $room->update([
                    'licence_no' => $request->licence_no,
                    'branch_id' => $request->branch_id,
                    'hosteler_details' => $request->hosteler_details,
                    'hosteler_id' => $request->hosteler_id,
                    'admission_date' => $request->admission_date,
                    'hosteler_name' => $request->hosteler_name,
                    'course_name' => $request->course_name,
                    'father_name' => $request->father_name,
                    'building' => $request->building,
                    'floor' => $request->floor,
                    'room_type' => $request->room_type,
                    'room_no' => $request->room_no,
                    'room_beds' => $request->room_beds
                ]);
                 $room = $room->load(['licence', 'branch','student']);

                 return response()->json([
                    'success' => true,
                    'message' => 'Room assign update successfully',
                    'data' => $room
                 ]);    

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Somthing want wrong',
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
            $room = Roomassign::findorFail($id);
            $room->delete();

            return response()->json([
                'message' => 'Room Assign deleted successfully'
            ],200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Somthing want wrong',
                'error' => $e->getmessage()
            ],500);
        }
    }
}
