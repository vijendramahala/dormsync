<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Prospect;
use App\Models\Licence;
use App\Models\Branch;
use Illuminate\Support\Facades\Validator;

class ProspectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $prospect = Prospect::with(['licence', 'branch'])->get();

     return response()->json([
        'status' => true,
        'data' => $prospect
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    private function prospectValidationRules()
{
    return [
        'licence_no' => 'nullable|exists:licences,licence_no',
        'branch_id' => 'nullable|exists:branches,id',
        'student_name' => 'required|string|max:255',
        'gender' => 'required|in:male,female,other',
        'contact_no' => 'required|string|max:15',
        'address' => 'required|string|max:500',
        'staff' => 'required|string|max:255',
        'next_appointment_date' => 'required|date|after_or_equal:today',
        'time' => 'required|date_format:H:i',
        'remark' => 'nullable|string|max:1000',
    ];
}


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->prospectValidationRules());

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
                $prospect = Prospect::create([
                    'licence_no' => $request->licence_no,
                    'branch_id' => $request->branch_id,
                    'student_name' => $request->student_name,
                    'gender' => $request->gender,
                    'contact_no' => $request->contact_no,
                    'address' => $request->address,
                    'staff' => $request->staff,
                    'next_appointment_date' => $request->next_appointment_date,
                    'time' => $request->time,
                    'remark' => $request->remark
                ]);
                $prospect = $prospect->load(['licence', 'branch']);

                return response()->json([
                'message' => 'prospect added successfully',
                'data' => $prospect
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
         $validator = Validator::make($request->all(), $this->prospectValidationRules());

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
                $prospect = Prospect::findorFail($id);
                $prospect->update([
                    'licence_no' => $request->licence_no,
                    'branch_id' => $request->branch_id,
                    'student_name' => $request->student_name,
                    'gender' => $request->gender,
                    'contact_no' => $request->contact_no,
                    'address' => $request->address,
                    'staff' => $request->staff,
                    'next_appointment_date' => $request->next_appointment_date,
                    'time' => $request->time,
                    'remark' => $request->remark
                ]);
                $prospect = $prospect->load(['licence', 'branch']);

                return response()->json([
                    'success' => true,
                    'message' => 'prospect update successsfully',
                    'data' => $prospect
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
            $prospect = Prospect::findorFail($id);

            $prospect->delete();

            return response()->json([
                'message' => 'prospect deleted successfully',
            ],200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Faild to delete prospect',
                'message' => $e->getmessage()
            ]);

        }
    }


    public function report(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:date_from',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = Prospect::whereBetween('next_appointment_date', [
                    $request->from_date,
                    $request->to_date
                ])->get();

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function createdAtReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Filter records created on that specific date
        $data = Prospect::whereDate('created_at', $request->date)->get();

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function filterProspects(Request $request)
        {
            $query = Prospect::query();

            // Dynamic column filter (e.g., address = 'jaipur', student_name = 'rahul')
            if ($request->has('column') && $request->has('value')) {
                $query->where($request->column, 'like', '%' . $request->value . '%');
            }

            // Exact match for specific fields if needed
            if ($request->has('gender')) {
                $query->where('gender', $request->gender);
            }

            if ($request->has('address')) {
                $query->where('address', $request->address);
            }

            // Filter by created_at (exact date)
            if ($request->has('created_at')) {
                $query->whereDate('created_at', $request->created_at);
            }

            // Filter between two dates (for next_appointment_date or created_at)
            if ($request->has('from_date') && $request->has('to_date')) {
                $query->whereBetween('next_appointment_date', [
                    $request->from_date,
                    $request->to_date
                ]);
            }

            $data = $query->get();

            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        }



}
