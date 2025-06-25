<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Feesentry;
use App\Models\Licence;
use App\Models\Branch;
use App\Models\Admissionform;
use App\Models\Roomassign;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FeesentryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $licenceno = Auth::user()->licence_no;
        $branchid = Auth::user()->branch_id;

        $fees = Feesentry::with([
            'licence:id,licence_no',
            'branch:id,branch_name,b_city'
        ])
        ->where('licence_no', $licenceno)
        ->where('branch_id', $branchid)
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'successfully',
            'data' => $fees
        ], 200);
    }

    private function validation()
    {
        return[
            'licence_no'        => 'nullable|string|exists:licences,licence_no',
            'branch_id'         => 'required|integer|exists:branches,id',
            'hosteler_details'  => 'nullable|string|max:1000',
            'hosteler_id'       => 'required|exists:admissionforms,student_id',
            'admission_date'    => 'required|date_format:d/m/Y',
            'hosteler_name'     => 'required|string|max:255',
            'course_name'       => 'required|string|max:255',
            'father_name'       => 'required|string|max:255',
            'total_amount'      => 'required|string|min:0',
            'discount'          => 'required|string|min:0',
            'total_remaining'    => 'required|string|min:0',
            'EMI_recived' => 'nullable',
            'EMI_total' => 'nullable',
            'other1' => 'nullable|string|max:255',
            'other2' => 'nullable|string|max:255',
            'other3' => 'nullable|string|max:255',
            'other4' => 'nullable|string|max:255',
            'other5' => 'nullable|string|max:255',

        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->validation());

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
            return response()->json([
                'error' => 'The selected branch does not belong to the provided licence_no.'
            ], 422);
        }
        $admission = Admissionform::where('student_id', $request->hosteler_id)->first();
            if (!$admission) {
                return response()->json(['error' => 'Invalid hosteler id.'], 404);
            }

        try {
            // Decode fees_structure if it's a string
            $feesStructure = $request->fees_structure;
            if (is_string($feesStructure)) {
                $feesStructure = json_decode($feesStructure, true);
            }

            // Validate each fees_structure item
            if (!is_array($feesStructure)) {
                return response()->json(['error' => 'Invalid fees_structure format.'], 422);
            }

            foreach ($feesStructure as $item) {
                if (!isset($item['fees_type']) || !isset($item['price']) || !isset($item['discount']) || !isset($item['remaining'])) {
                    return response()->json([
                        'error' => 'Each fees_structure item must contain fees_type and price.'
                    ], 422);
                }
            }

            $fees = Feesentry::create([
                'licence_no' => $request->licence_no,
                'branch_id' => $request->branch_id,
                'hosteler_details' => $request->hosteler_details,
                'hosteler_id' => $request->hosteler_id,
                'admission_date' => $request->admission_date,
                'hosteler_name' => $request->hosteler_name,
                'course_name' => $request->course_name,
                'father_name' => $request->father_name,
                'fees_structure' => $feesStructure,
                'total_amount' => $request->total_amount,
                'discount' => $request->discount,
                'total_remaining' => $request->total_remaining,
                'EMI_recived' => $request->EMI_recived ?? 0,
                'EMI_total' => $request->EMI_total ?? 1,
                'other1' => $request->other1,
                'other2' => $request->other2,
                'other3' => $request->other3,
                'other4' => $request->other4,
                'other5' => $request->other5,
            ]);

            $fees = $fees->load(['licence', 'branch','student']);

            return response()->json([
                'status' => true,
                'message' => 'Fees Entry added successfully',
                'data' => $fees
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), $this->validation());

        if ($validator->fails()) {
            return response()->json(['status' => false,'message' => $validator->errors()->first()], 200);
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
            try {

                $fees = Feesentry::findorFail($id);

                 // Decode fees_structure if it's a string
            $feesStructure = $request->fees_structure;
            if (is_string($feesStructure)) {
                $feesStructure = json_decode($feesStructure, true);
            }

            // Validate each fees_structure item
            if (!is_array($feesStructure)) {
                return response()->json(['error' => 'Invalid fees_structure format.'], 422);
            }

            foreach ($feesStructure as $item) {
                if (!isset($item['fees_type']) || !isset($item['price']) || !isset($item['discount']) || !isset($item['remaining'])) {
                    return response()->json([
                        'error' => 'Each fees_structure item must contain fees_type and price.'
                    ], 422);
                }
            }

                $fees->update([
                    'licence_no' => $request->licence_no,
                    'branch_id' => $request->branch_id,
                    'hosteler_details' => $request->hosteler_details,
                    'hosteler_id' => $request->hosteler_id,
                    'admission_date' => $request->admission_date,
                    'hosteler_name' => $request->hosteler_name,
                    'course_name' => $request->course_name,
                    'father_name' => $request->father_name,
                    'fees_structure' => $feesStructure,
                    'total_amount' => $request->total_amount,
                    'discount' => $request->discount,
                    'total_remaining' => $request->total_remaining,
                    'EMI_recived' => $request->EMI_recived ?? 0,
                    'EMI_total' => $request->EMI_total ?? 1,
                    'other1' => $request->other1,
                    'other2' => $request->other2,
                    'other3' => $request->other3,
                    'other4' => $request->other4,
                    'other5' => $request->other5,
                ]);
                $fees = $fees->load(['licence', 'branch','student']);

                 return response()->json([
                    'status' => true,
                    'message' => 'Fees Entry update successfully',
                    'data' => $fees
                 ], 200); 
            } catch (\Exception $e){
                return response()->json([
                'status' => false,
                'message' => 'Somthing want wrong',
                'error' => $e->getmessage()
                ]. 500);
            }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $fees = Feesentry::findorFail($id);
            $fees->delete();

            return response()->json([
                'status' => true,
                'message' => 'Fees Entry deleted successfully'
            ],200);
        }catch (\Exception $e){
            return respose()->json([
                'status' => false,
                'message' => 'Somthing want wrong',
                'error' => $e->getmessage()
            ], 500);
        }
    }
   public function getCombinedData(Request $request, $licence_no)
    {
        try {
            $fromDate = null;
            $toDate = null;

            if ($request->filled('from_date')) {
                $fromDate = Carbon::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            }

            if ($request->filled('to_date')) {
                $toDate = Carbon::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            }

            // Fees query with date filtering
            $feesQuery = Feesentry::where('licence_no', $licence_no);

            if ($fromDate) {
                $feesQuery->whereRaw("STR_TO_DATE(admission_date, '%d/%m/%Y') >= ?", [$fromDate]);
            }

            if ($toDate) {
                $feesQuery->whereRaw("STR_TO_DATE(admission_date, '%d/%m/%Y') <= ?", [$toDate]);
            }

            $feesList = $feesQuery->get();
            $roomList = Roomassign::where('licence_no', $licence_no)->get();

            if ($feesList->isEmpty() || $roomList->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data not found for the provided licence_no or date range.'
                ], 200);
            }

            $response = [];

            foreach ($feesList as $fees) {
                $room = $roomList->firstWhere('hosteler_id', $fees->hosteler_id);

                if ($room) {
                    $fees_structure = is_array($fees->fees_structure)
                        ? $fees->fees_structure
                        : json_decode($fees->fees_structure, true);

                    $prices = [];
                    foreach ($fees_structure as $item) {
                        if (isset($item['price'])) {
                            $prices[] = $item['price'];
                        }
                    }

                    $response[] = [
                        'hosteler_id'     => $room->hosteler_id,
                        'hosteler_name'   => $room->hosteler_name,
                        'father_name'     => $room->father_name,
                        'admission_date'  => $fees->admission_date,
                        'floor_id'        => $room->floor_id,
                        'room_type'       => $room->room_type,
                        'EMI_total'       => $fees->EMI_total,
                        'EMI_recived'     => $fees->EMI_recived,
                        'total_remaining' => $fees->total_remaining,
                        'prices'          => $prices,
                    ];  
                }
            }

            return response()->json([
                'status' => true,
                'data' => $response
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}