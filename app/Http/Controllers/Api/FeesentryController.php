<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Feesentry;
use App\Models\Licence;
use App\Models\Branch;
use App\Models\Roomassign;
use Illuminate\Support\Facades\Validator;

class FeesentryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fees = Feesentry::with(['licence', 'branch'])->get();

     return response()->json([
        'status' => true,
        'data' => $fees
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }
    private function validation()
    {
        return[
            'licence_no'        => 'nullable|string|exists:licences,licence_no',
            'branch_id'         => 'required|integer|exists:branches,id',
            'hosteler_details'  => 'nullable|string|max:1000',
            'hosteler_id'       => 'required|string|max:50',
            'admission_date'    => 'required|date',
            'hosteler_name'     => 'required|string|max:255',
            'course_name'       => 'required|string|max:255',
            'father_name'       => 'required|string|max:255',
            'total_amount'      => 'required|integer|min:0',
            'discount'          => 'required|integer|min:0',
            'total_remaining'    => 'required|integer|min:0',
            'EMI_recived' => 'nullable',
            'EMI_total' => 'nullable'

        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->validation());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()], 422);
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
                if (!isset($item['fees_type']) || !isset($item['price'])) {
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
            ]);

            $fees = $fees->load(['licence', 'branch']);

            return response()->json([
                'message' => 'Fees Entry added successfully',
                'data' => $fees
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
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
        $validator = Validator::make($request->all(), $this->validation());

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
                if (!isset($item['fees_type']) || !isset($item['price'])) {
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
                ]);
                $fees = $fees->load(['licence', 'branch']);

                 return response()->json([
                    'success' => true,
                    'message' => 'Fees Entry update successfully',
                    'data' => $fees
                 ]); 
            } catch (\Exception $e){
                return response()->json([
                'success' => false,
                'message' => 'Somthing want wrong',
                'error' => $e->getmessage()
                ]);
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
                'success' => true,
                'message' => 'Fees Entry deleted successfully'
            ],200);
        }catch (\Exception $e){
            return respose()->json([
                'success' => false,
                'message' => 'Somthing want wrong',
                'error' => $e->getmessage()
            ],500);
        }
    }
   public function getCombinedData($licence_no)
        {
            try {
                $feesList = Feesentry::where('licence_no', $licence_no)->get();
                $roomList = Roomassign::where('licence_no', $licence_no)->get();

                if ($feesList->isEmpty() || $roomList->isEmpty()) {
                    return response()->json([
                        'error' => 'Data not found for the provided licence_no.'
                    ], 404);
                }

                $response = [];

                foreach ($feesList as $fees) {
                    // Room match for same hosteler_id
                    $room = $roomList->firstWhere('hosteler_id', $fees->hosteler_id);

                    if ($room) {
                        // Decode JSON if needed
                        $fees_structure = is_array($fees->fees_structure) ? $fees->fees_structure : json_decode($fees->fees_structure, true);

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
                            'admission_date'  => $room->admission_date,
                            'EMI_total'       => $fees->EMI_total,
                            'EMI_recived'     => $fees->EMI_recived,
                            'total_remaining' => $fees->total_remaining,
                            'prices'          => $prices
                        ];
                    }
                }

                return response()->json([
                    'data' => $response
                ], 200);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

}
