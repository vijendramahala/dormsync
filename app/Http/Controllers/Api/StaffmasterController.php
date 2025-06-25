<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\staffmaster;
use App\Models\Licence;
use App\Models\Branch;
use App\Models\Ledgermaster;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class StaffmasterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $licenceNo = Auth::user()->licence_no; // Ensure user is authenticated and has this column
        $branchId = Auth::user()->branch_id;

        $staff = staffmaster::with([
        'licence:id,licence_no', // Select only specific licence columns
        'branch:id,branch_name,b_city'// Select only specific branch columns
    ])
            ->where('licence_no', $licenceNo)
            ->where('branch_id', $branchId)
            ->get();

        return response()->json([
            'status' => true,
            'data' => $staff
        ], 200);
    }


    public function store(Request $request)
    {
    //   return response()->json($request->all());
        $validator = Validator::make($request->all(), [
            'licence_no' => 'nullable|exists:licences,licence_no',
            'branch_id' => 'nullable|exists:branches,id',
            'title' => 'required|in:Mr,Miss,Mrs,Dr', 
            'staff_name' => 'required',
            'relation_type' => 'required|in:S/O,D/O,W/O',
            'name' => 'required',
            'upload_file' => 'nullable|array',
            'upload_file.*' => 'file|mimes:jpeg,png,pdf,docx|max:2048',
            'contact_no' => 'required|string|max:15',
            'whatsapp_no' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:255',
            'department' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'joining_date' => 'required|date_format:d/m/Y',
            'aadhar_no' => 'nullable|string|size:12',
            'permanent_address' => 'required|string|max:500',
            'state' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'city_town_village' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'pin_code' => 'required|string|size:6',
            'temporary_address' => 'nullable|string|max:500',
            'other1' => 'nullable|string|max:255',
            'other2' => 'nullable|string|max:255',
            'other3' => 'nullable|string|max:255',
            'other4' => 'nullable|string|max:255',
            'other5' => 'nullable|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);
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
            $staff = staffmaster::create([
                    'licence_no' => $request->licence_no,
                    'branch_id' => $request->branch_id,
                'title' => $request->title,
                'staff_name' => $request->staff_name,
                'relation_type' => $request->relation_type,
                'name' => $request->name,
                'contact_no' => $request->contact_no,
                'whatsapp_no' => $request->whatsapp_no,
                'email' => $request->email,
                'department' => $request->department,
                'designation' => $request->designation,
                'joining_date' => $request->joining_date,
                'aadhar_no' => $request->aadhar_no,
                'permanent_address' => $request->permanent_address,
                'state' => $request->state,
                'city' => $request->city,
                'city_town_village' => $request->city_town_village,
                'address' => $request->address,
                'pin_code' => $request->pin_code,
                'temporary_address' => $request->temporary_address,
                'other1' => $request->other1,
                'other2' => $request->other2,
                'other3' => $request->other3,
                'other4' => $request->other4,
                'other5' => $request->other5,
            ]);
         if ($request->hasFile('uplode_file')) {
            $staff->clearMediaCollection('uplode_file');
            foreach ($request->file('uplode_file') as $file) {
                if ($file->isValid()) {
                    $staff->addMedia($file)->toMediaCollection('uplode_file');
                }
            }
        }
            if ($request->hasFile('document_uplode') && $request->file('document_uplode')->isValid()) {
            $staff->addMediaFromRequest('document_uplode')->toMediaCollection('document_uplode');
        }
            
        
            $staff = $staff->load(['licence', 'branch']);

            return response()->json([
                'status' => true,
                'message' => 'Staff added successfully',
                'data' => $staff
            ], 200);
        
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
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
// dd($request->all());
    //  return response()->json($request->all());
    $validator = Validator::make($request->all(), [
            'licence_no' => 'nullable|exists:licences,licence_no',
            'branch_id' => 'nullable|exists:branches,id',
            'title' => 'required|in:Mr,Miss,Mrs,Dr', 
            'staff_name' => 'required',
            'relation_type' => 'required|in:S/O,D/O,W/O',
            'name' => 'required',
            'upload_file' => 'nullable|array',
            'upload_file.*' => 'file|mimes:jpeg,png,pdf,docx|max:2048',
            'contact_no' => 'required|string|max:15',
            'whatsapp_no' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:255',
            'department' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'joining_date' => 'required|date_format:d/m/Y',
            'aadhar_no' => 'nullable|string|size:12',
            'permanent_address' => 'required|string|max:500',
            'state' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'city_town_village' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'pin_code' => 'required|string|size:6',
            'temporary_address' => 'nullable|string|max:500',
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
            return response()->json(['error' => 'Invalid licence.'], 404);
        }

        $branch = Branch::where('id', $request->branch_id) // 🔧 Corrected from $request->id to $request->branch_id
                        ->where('licence_no', $licence->licence_no)
                        ->first();

        if (!$branch) {
            return response()->json(['error' => 'The selected branch does not belong to the given licence.'], 422);
        }

     try {
        // ✅ Step 1: Find the staff record by ID
        $staff = staffmaster::findOrFail($id);

        // ✅ Step 2: Update other fields (text fields)
        $staff->update($request->only([
            'licence_no',
            'branch_id',
            'title',
            'staff_name',
            'relation_type',
            'name',
            'contact_no',
            'whatsapp_no',
            'email',
            'department',
            'designation',
            'joining_date',
            'aadhar_no',
            'permanent_address',
            'state',
            'city',
            'city_town_village',
            'address',
            'pin_code',
            'temporary_address',
            'other1',
            'other2',
            'other3',
            'other4',
            'other5',
        ]));
        if ($request->hasFile('uplode_file')) {
            $staff->clearMediaCollection('uplode_file');

            foreach ($request->file('uplode_file') as $file) {
                if ($file->isValid()) {
                    $staff->addMedia($file)->toMediaCollection('uplode_file');
                }
            }
        }

        // ✅ Step 4: Handle single file for 'document_upload'
        if ($request->hasFile('document_uplode') && $request->file('document_uplode')->isValid()) {
            // Optional: Clear old document from 'document_upload' collection
            $staff->clearMediaCollection('document_uplode');

            // Add the new single document to the 'document_upload' media collection
            $staff->addMediaFromRequest('document_uplode')->toMediaCollection('document_uplode');
        }

        $staff = $staff->load(['licence', 'branch']);

        return response()->json([
            'status' => true,
            'message' => 'Staff updated successfully',
            'data' => $staff
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong',
            'error' => $e->getMessage()
        ], 500);
    }
}

    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $staff = staffmaster::findOrFail($id);

            // Delete associated media files
            $staff->clearMediaCollection('uplode_file');
            $staff->clearMediaCollection('document_uplode');

            // Now delete the record
            $staff->delete();

            return response()->json([ 'status' => true, 'message' => 'Staff and associated files deleted successfully'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete staff', 'message' => $e->getMessage()], 500);
        }
    }

}
