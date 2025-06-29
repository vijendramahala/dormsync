<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Leaveapplication;
use App\Models\Licence;
use App\Models\Branch;
use App\Models\Admissionform;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class LeaveapplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       $licenceno = Auth::user()->licence_no;
        $branchid = Auth::user()->branch_id;

        $leave = Leaveapplication::with([
            'licence:id,licence_no',
            'branch:id,branch_name,b_city'
        ])
        ->where('licence_no', $licenceno)
        ->where('branch_id', $branchid)
        ->get();

        return response()->json([
            'status' => true,
            'data' => $leave
        ], 200);
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
        'from_date'         => 'required|date',
        'to_date'           => 'required|date|after_or_equal:from_date',
        'accompained_by'    => 'nullable|string|max:255',
        'relation'          => 'nullable|string|max:100',
        'aadhar_no'         => 'required|string|size:12|regex:/^[0-9]{12}$/',
        'contact'           => 'required|string|regex:/^[0-9]{10}$/',
        'destination'       => 'required|string|max:255',
        'purpose_of_leave'  => 'required|string|max:500',
        'attachment'        => 'nullable|file|mimes:jpeg,png,pdf,docx|max:2048',
        'other1' => 'nullable|string|max:255',
        'other2' => 'nullable|string|max:255',
        'other3' => 'nullable|string|max:255',
        'other4' => 'nullable|string|max:255',
        'other5' => 'nullable|string|max:255',
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
                $leave = Leaveapplication::create([
                    'licence_no' => $request->licence_no,
                    'branch_id' => $request->branch_id,
                    'hosteler_details' => $request->hosteler_details,
                    'hosteler_id' => $request->hosteler_id,
                    'admission_date' => $request->admission_date,
                    'hosteler_name' => $request->hosteler_name,
                    'course_name' => $request->course_name,
                    'father_name' => $request->father_name,
                    'from_date' => $request->from_date,
                    'to_date' => $request->	to_date,
                    'accompained_by' => $request->accompained_by,
                    'relation' => $request->relation,
                    'aadhar_no' => $request->aadhar_no,
                    'contact' => $request->contact,
                    'destination' => $request->destination,
                    'purpose_of_leave' => $request->purpose_of_leave,
                    'other1' => $request->other1,
                    'other2' => $request->other2,
                    'other3' => $request->other3,
                    'other4' => $request->other4,
                    'other5' => $request->other5,
                ]);
                if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
                    $leave->addMediaFromRequest('attachment')->toMediaCollection('attachment');
                }

                $leave = $leave->load(['licence', 'branch','student']);

                return response()->json([
                'message' => 'leave form added successfully',
                'data' => $leave
            ], 201);    

            }catch (\Exception $e){
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong',
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
        'from_date'         => 'required|date',
        'to_date'           => 'required|date|after_or_equal:from_date',
        'accompained_by'    => 'nullable|string|max:255',
        'relation'          => 'nullable|string|max:100',
        'aadhar_no'         => 'required|string|size:12|regex:/^[0-9]{12}$/',
        'contact'           => 'required|string|regex:/^[0-9]{10}$/',
        'destination'       => 'required|string|max:255',
        'purpose_of_leave'  => 'required|string|max:500',
        'attachment'        => 'nullable|file|mimes:jpeg,png,pdf,docx|max:2048',
        'other1' => 'nullable|string|max:255',
        'other2' => 'nullable|string|max:255',
        'other3' => 'nullable|string|max:255',
        'other4' => 'nullable|string|max:255',
        'other5' => 'nullable|string|max:255',
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
                $leave = Leaveapplication::findorFail($id);

                $leave->update([
                    'licence_no' => $request->licence_no,
                    'branch_id' => $request->branch_id,
                    'hosteler_details' => $request->hosteler_details,
                    'hosteler_id' => $request->hosteler_id,
                    'admission_date' => $request->admission_date,
                    'hosteler_name' => $request->hosteler_name,
                    'course_name' => $request->course_name,
                    'father_name' => $request->father_name,
                    'from_date' => $request->from_date,
                    'to_date' => $request->	to_date,
                    'accompained_by' => $request->accompained_by,
                    'relation' => $request->relation,
                    'aadhar_no' => $request->aadhar_no,
                    'contact' => $request->contact,
                    'destination' => $request->destination,
                    'purpose_of_leave' => $request->purpose_of_leave,
                    'other1' => $request->other1,
                    'other2' => $request->other2,
                    'other3' => $request->other3,
                    'other4' => $request->other4,
                    'other5' => $request->other5,
                ]);
                if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
                    $leave->clearMediaCollection('attachment');
                $leave->addMediaFromRequest('attachment')->toMediaCollection('attachment');
                }

                 $leave = $leave->load(['licence', 'branch','student']);

                return response()->json([
                    'success' => true,
                    'message' => 'leave form update successsfully',
                    'data' => $leave
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
            $leave = Leaveapplication::findorFail($id);

            $leave->clearMediaCollection('attachment');

            $leave->delete();

            return response()->json([
                'message' => 'leave form deleted successfully',
            ],200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Faild to delete visitor form',
                'message' => $e->getmessage()
            ]);

        }
    }
}
