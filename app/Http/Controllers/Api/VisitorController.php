<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Visitor;
use App\Models\Licence;
use App\Models\Branch;
use App\Models\Admissionform;
use Illuminate\Support\Facades\Validator;

class VisitorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $visitor = Visitor::with(['licence', 'branch','student'])->get();

     return response()->json([
        'status' => true,
        'data' => $visitor
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
            'branch_id' => 'nullable|exists:branches,id',
            'hosteler_details' => 'nullable|string|max:1000',
            'hosteler_id' => 'required',
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
                ]);
                if ($request->hasFile('visitor_document') && $request->file('visitor_document')->isValid()) {
                    $visitor->addMediaFromRequest('visitor_document')->toMediaCollection('visitor_document');
                }
                
                $visitor = $visitor->load(['licence', 'branch','student']);

                return response()->json([
                'message' => 'visitor form added successfully',
                'data' => $visitor
            ], 201);

            } catch (\Exception $e){
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
            'licence_no' => 'nullable|exists:licences,licence_no',
            'branch_id' => 'nullable|exists:branches,id',
            'hosteler_details' => 'nullable|string|max:1000',
            'hosteler_id' => 'required',
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
                ]);
                if ($request->hasFile('visitor_document') && $request->file('visitor_document')->isValid()) {
                    $visitor->clearMediaCollection('visitor_document');
                $visitor->addMediaFromRequest('visitor_document')->toMediaCollection('visitor_document');
                }

                $visitor = $visitor->load(['licence', 'branch','student']);

                return response()->json([
                    'success' => true,
                    'message' => 'visitor form update successsfully',
                    'data' => $visitor
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
            $visitor = Visitor::findorFail($id);

            $visitor->clearMediaCollection('visitor_document');

            $visitor->delete();

            return response()->json([
                'message' => 'visitor form deleted successfully',
            ],200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Faild to delete visitor form',
                'message' => $e->getmessage()
            ]);

        }
    }
}
