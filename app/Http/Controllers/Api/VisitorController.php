<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Visitor;
use App\Models\Licence;
use App\Models\Branch;
use App\Models\Admissionform;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class VisitorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       $licenceno = Auth::user()->licence_no;
        $branchid = Auth::user()->branch_id;

        $visitor = Visitor::with([
            'licence:id,;icence_no',
            'branch:id,branch_name,b_city'
        ])
        ->where('licence_no', $licenceno)
        ->where('branch_id', $branchid)
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'successfully',
            'data' => $visitor
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'licence_no' => 'nullable|exists:licences,licence_no',
            'branch_id' => 'nullable|exists:branches,id',
            'hosteler_details' => 'nullable|string|max:1000',
            'hosteler_id' => 'required|exists:admissionforms,student_id',
            'admission_date' => 'required|date',
            'hosteler_name' => 'required|string|max:255',
            'course_name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'visiting_date' => 'required|date',
            'visitor_name' => 'required|string|max:255',
            'relation' => 'required|string|max:100',
            'contact' => 'required|string|regex:/^[0-9]{10}$/',
            'aadhar_no' => 'required|string|size:12|regex:/^[0-9]{12}$/',
            'purpose_of_visit' => 'required|string|max:500',
            'date_of_leave' => 'nullable|date|after_or_equal:visiting_date',
            'visitor_document' => 'nullable|file|mimes:jpeg,png,pdf,docx|max:2048',
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
                $visitor = Visitor::create([
                    'licence_no' => $request->licence_no,
                    'branch_id' => $request->branch_id,
                    'hosteler_details' => $request->hosteler_details,
                    'hosteler_id' => $request->hosteler_id,
                    'admission_date' => $request->admission_date,
                    'hosteler_name' => $request->hosteler_name,
                    'course_name' => $request->course_name,
                    'father_name' => $request->father_name,
                    'visiting_date' => $request->visiting_date,
                    'visitor_name' => $request->visitor_name,
                    'relation' => $request->relation,
                    'contact' => $request->contact,
                    'aadhar_no' => $request->aadhar_no,
                    'purpose_of_visit' => $request->purpose_of_visit,
                    'date_of_leave' => $request->date_of_leave,
                    'other1' => $request->other1,
                    'other2' => $request->other2,
                    'other3' => $request->other3,
                    'other4' => $request->other4,
                    'other5' => $request->other5,
                ]);
                if ($request->hasFile('visitor_document') && $request->file('visitor_document')->isValid()) {
                    $visitor->addMediaFromRequest('visitor_document')->toMediaCollection('visitor_document');
                }
                
                $visitor = $visitor->load(['licence', 'branch','student']);

                return response()->json([
                    'status' => true,
                'message' => 'visitor form added successfully',
                'data' => $visitor
            ], 200);

            } catch (\Exception $e){
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong',
                    'error' => $e->getmessage()
                ],500);
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
            'hosteler_details' => 'nullable|string|max:1000',
            'hosteler_id' => 'required|exists:admissionforms,student_id',
            'admission_date' => 'required|date',
            'hosteler_name' => 'required|string|max:255',
            'course_name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'visiting_date' => 'required|date',
            'visitor_name' => 'required|string|max:255',
            'relation' => 'required|string|max:100',
            'contact' => 'required|string|regex:/^[0-9]{10}$/',
            'aadhar_no' => 'required|string|size:12|regex:/^[0-9]{12}$/',
            'purpose_of_visit' => 'required|string|max:500',
            'date_of_leave' => 'nullable|date|after_or_equal:visiting_date',
            'visitor_document' => 'nullable|file|mimes:jpeg,png,pdf,docx|max:2048',
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
                $visitor = Visitor::findorFail($id);

                $visitor->update([
                    'licence_no' => $request->licence_no,
                    'branch_id' => $request->branch_id,
                    'hosteler_details' => $request->hosteler_details,
                    'hosteler_id' => $request->hosteler_id,
                    'admission_date' => $request->admission_date,
                    'hosteler_name' => $request->hosteler_name,
                    'course_name' => $request->course_name,
                    'father_name' => $request->father_name,
                    'visiting_date' => $request->visiting_date,
                    'visitor_name' => $request->visitor_name,
                    'relation' => $request->relation,
                    'contact' => $request->contact,
                    'aadhar_no' => $request->aadhar_no,
                    'purpose_of_visit' => $request->purpose_of_visit,
                    'date_of_leave' => $request->date_of_leave,
                    'other1' => $request->other1,
                    'other2' => $request->other2,
                    'other3' => $request->other3,
                    'other4' => $request->other4,
                    'other5' => $request->other5,
                ]);
                if ($request->hasFile('visitor_document') && $request->file('visitor_document')->isValid()) {
                    $visitor->clearMediaCollection('visitor_document');
                $visitor->addMediaFromRequest('visitor_document')->toMediaCollection('visitor_document');
                }

                $visitor = $visitor->load(['licence', 'branch','student']);

                return response()->json([
                    'status' => true,
                    'message' => 'visitor form update successsfully',
                    'data' => $visitor
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                'status' => false,
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
            $visitor = Visitor::findorFail($id);

            $visitor->clearMediaCollection('visitor_document');

            $visitor->delete();

            return response()->json([
                'status' => true,
                'message' => 'visitor form deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Faild to delete visitor form',
                'message' => $e->getmessage()
            ], 500);

        }
    }
}
