<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Companydetail;
use App\Models\Branch;
use App\Models\Licence;
use Illuminate\Support\Facades\Validator;

class CompanydetailCntroller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $company = Companydetail::with(['licence', 'branch'])->get();

    return response()->json([
        'status' => true,
        'data' => $company
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
        'licence_no'          => 'nullable|exists:licences,licence_no',
        'branch_id'           => 'nullable|exists:branches,id',
        'business_name'       => 'required|string|max:255',
        'business_type'       => 'required|string|max:255',
        'owner_name'          => 'required|string|max:255',
        'email'               => 'required|email',
        'mobile_number'       => 'required|string|regex:/^[0-9]{10,15}$/',
        'landline_number'     => 'nullable|string|regex:/^[0-9\-]{6,15}$/',
        'business_address'    => 'required|string',
        'pin_code'            => 'required|string|size:6',
        'std_code'            => 'nullable|string|max:10',
        'state'               => 'required|string|max:100',
        'city'                => 'required|string|max:100',
        'district_or_town'    => 'required|string|max:100',
        'additional_info'     => 'nullable|string',
        'information_1'       => 'nullable|string|max:255',
        'information_2'       => 'nullable|string|max:255',
        'information_3'       => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()], 422);
        }
         // Step 1: Licence check
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
            $company = Companydetail::create([
                'licence_no' => $request->licence_no,
                'branch_id' => $request->branch_id,
                'business_name' => $request->business_name,
                'business_type' => $request->business_type,
                'owner_name' => $request->owner_name,
                'email' => $request->email,
                'mobile_number' => $request->mobile_number,
                'landline_number' => $request->landline_number,
                'business_address' => $request->business_address,
                'pin_code' => $request->pin_code,
                'std_code' => $request->std_code,
                'state' => $request->state,
                'city' => $request->city,
                'district_or_town' => $request->district_or_town,
                'additional_info' => $request->additional_info,
                'information_1' => $request->information_1,
                'information_2' => $request->information_2,
                'information_3' => $request->information_3
            ]);

            $company = $company->load(['licence', 'branch']);

            return response()->json(['message' => 'Company detail added successfully', 'data' => $company], 201);


        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
{
    $company = Companydetail::with(['licence', 'branch'])->findOrFail($id);

    return response()->json([
        'status' => true,
        'data' => $company
    ]);
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
        'licence_no'          => 'nullable|exists:licences,licence_no',
        'branch_id'           => 'nullable|exists:branches,id',
        'business_name'       => 'required|string|max:255',
        'business_type'       => 'required|string|max:255',
        'owner_name'          => 'required|string|max:255',
        'email'               => 'required|email',
        'mobile_number'       => 'required|string|regex:/^[0-9]{10,15}$/',
        'landline_number'     => 'nullable|string|regex:/^[0-9\-]{6,15}$/',
        'business_address'    => 'required|string',
        'pin_code'            => 'required|string|size:6',
        'std_code'            => 'nullable|string|max:10',
        'state'               => 'required|string|max:100',
        'city'                => 'required|string|max:100',
        'district_or_town'    => 'required|string|max:100',
        'additional_info'     => 'nullable|string',
        'information_1'       => 'nullable|string|max:255',
        'information_2'       => 'nullable|string|max:255',
        'information_3'       => 'nullable|string|max:255',
    ]);

    // If validation fails, return errors
    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()->first()], 422);
    }

    try {
        // Find the company by its ID
        $company = Companydetail::findOrFail($id);

        // Update the company details
        $company->update($request->only([
            'licence_no',
            'branch_id',
            'business_name',
            'business_type',
            'owner_name',
            'email',
            'mobile_number',
            'landline_number',
            'business_address',
            'pin_code',
            'std_code',
            'state',
            'city',
            'district_or_town',
            'additional_info',
            'information_1',
            'information_2',
            'information_3',
        ]));

        // If branch_id is provided, update the associated branch
        if ($request->has('branch_id')) {
            $branch = Branch::find($request->branch_id);
            
                if ($branch) {
               $branch->update([
                'name' => $request->owner_name,
                'contact_no' => $request->mobile_number,
                'state' => $request->state,
                'city' => $request->city,
            ]);
         }

        }

        $company = $company->load(['licence', 'branch']);

        return response()->json([
            'success' => true,
            'message' => 'Company and associated branch updated successfully',
            'company' => $company
        ], 200);


    } catch (\Exception $e) {
        // Handle any errors
        return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
    }
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $company = Companydetail::findOrFail($id);

            $company->delete();

             return response()->json(['message' => 'Staff and associated files deleted successfully'], 200);

        } catch (\Exception $e) {

                return response()->json(['error' => 'Failed to delete staff', 'message' => $e->getMessage()], 500);
        }
    }
}
