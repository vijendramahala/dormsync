<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Admissionform;
use App\Models\Licence;
use App\Models\Branch;
use App\Models\Ledgermaster;
use Illuminate\Support\Facades\Auth;

class AdmissionformController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $licenceno = Auth::user()->licence_no;
        $branchid = Auth::user()->branch_id;

        $admission = Admissionform::with([
            'licence:id,;icence_no',
            'branch:id,branch_name,b_city'
        ])
        ->where('licence_no', $licenceno)
        ->where('branch_id', $branchid)
        ->get();

        return response()->json([
            'status' => true,
            'data' => $admission
        ], 200);
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }
    private function validation(){

        return[
       'licence_no' => 'exists:licences,licence_no',
        'branch_id' => 'exists:branches,id',
        'admission_date' => 'required|date',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'student_id' => 'required|string|max:50',
        'student_name' => 'required|string|max:255',
        'gender' => 'required|string|in:male,female,other',
        'marital_status' => 'required|string|in:single,married,divorced,widowed',
        'aadhar_no' => 'required|string|size:12',
        'upload_file' => 'nullable|array',
        'upload_file.*' => 'file|max:2048',
        'caste' => 'required|string|max:100',
        'primary_contact_no' => 'required|string|regex:/^[0-9]{10}$/',
        'whatsapp_no' => 'nullable|string|regex:/^[0-9]{10}$/',
        'email' => 'required|email|max:255',
        'college_name' => 'required|string|max:255',
        'course' => 'required|string|max:100',
        'date_of_birth' => 'required|date',
        'year' => 'required|string|max:20',
        'father_name' => 'required|string|max:255',
        'mother_name' => 'required|string|max:255',
        'guardian' => 'nullable|string|max:255',
        'emergency_no' => 'required|string|regex:/^[0-9]{10}$/',
        'permanent_address' => 'required|string|max:500',
        'permanent_state' => 'required|string|max:100',
        'permanent_city' => 'required|string|max:100',
        'permanent_city_town' => 'required|string|max:100',
        'permanent_pin_code' => 'required|string|size:6',
        'temporary_address' => 'nullable|string|max:500',
        'temporary_state' => 'nullable|string|max:100',
        'temporary_city' => 'nullable|string|max:100',
        'temporary_city_town' => 'nullable|string|max:100',
        'temporary_pin_code' => 'nullable|string|size:6',
        ];
    }
    private function lager(){
        return [
            'licence_no' => 'required|string|exists:licences,licence_no',
            'branch_id' => 'required|integer|exists:branches,id',
            'title' => 'required|string|max:100',
            'relation_type' => 'required|in:S/O,D/O,W/O',
            'ledger_file' => 'nullable|array',
            'ledger_file.*' => 'file|max:2048',
            'contact_no' => 'nullable|string|regex:/^[0-9]{10}$/',
            'whatsapp_no' => 'nullable|string|regex:/^[0-9]{10}$/',
            'email' => 'nullable|email|max:255',
            'ledger_group' => 'required|string|max:255',
            'opening_balance' => 'required|numeric|min:0',
            'opening_type' => 'required|in:cr,br',
            'gst_no' => 'nullable|string|max:15',
            'aadhar_no' => 'nullable|string|digits:12',
            'l_docu_uplode' => 'nullable|file|max:2048',
            'permanent_address' => 'nullable|string|max:500',
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
{
    
    // Validate incoming request
    $validator = Validator::make($request->all(), $this->validation());

    if ($validator->fails()) {
        return response()->json(['status' => false, 'message' => $validator->errors()], 200);
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
        // Create the admission entry in the database
        $admission = Admissionform::create([
            'licence_no' => $request->licence_no,
            'branch_id' => $request->branch_id,
            'ledger_id' => $request->ledger_id,
            'admission_date' => $request->admission_date,
            'student_id' => $request->student_id,
            'student_name' => $request->student_name,
            'gender' => $request->gender,
            'marital_status' => $request->marital_status,
            'age' => $request->age,
            'aadhar_no' => $request->aadhar_no,
            'caste' => $request->caste,
            'primary_contact_no' => $request->primary_contact_no,
            'whatsapp_no' => $request->whatsapp_no,
            'email' => $request->email,
            'college_name' => $request->college_name,
            'course' => $request->course,
            'date_of_birth' => $request->date_of_birth,
            'year' => $request->year,
            'father_name' => $request->father_name,
            'mother_name' => $request->mother_name,
            'guardian' => $request->guardian,
            'emergency_no' => $request->emergency_no,
            'permanent_address' => $request->permanent_address,
            'permanent_state' => $request->permanent_state,
            'permanent_city' => $request->permanent_city,
            'permanent_city_town' => $request->permanent_city_town,
            'permanent_pin_code' => $request->permanent_pin_code,
            'temporary_address' => $request->temporary_address,
            'temporary_state' => $request->temporary_state,
            'temporary_city' => $request->temporary_city,
            'temporary_city_town' => $request->temporary_city_town,
            'temporary_pin_code' => $request->temporary_pin_code,
        ]);
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $admission->addMediaFromRequest('image')->toMediaCollection('image');
        }
        if ($request->hasFile('upload_file')) {
            $admission->clearMediaCollection('upload_file');
            foreach ($request->file('upload_file') as $file) {
                if ($file->isValid()) {
                    $admission->addMedia($file)->toMediaCollection('upload_file');
                }
            }
        }

        $uploadFiles = $admission->getMedia('upload_file')->map(function ($media) {
            return $media->getUrl(); 
        $image = $admission->getFirstMediaUrl('image');

        });
            $ledgerValidator = Validator::make($request->all(), $this->lager());

        if ($ledgerValidator->fails()) {
            return response()->json(['status' => false, 'message' => $ledgerValidator->errors()->first()], 200);
        }
        
         $ledger = Ledgermaster::create([
            'licence_no' => $admission->licence_no,
            'branch_id' => $admission->branch_id,
            'student_id' => $admission->id,
            'title' => $request->title,
            'ledger_name' => $admission->student_name,
            'relation_type' => $request->relation_type,
            'name' => $admission->father_name,
            'contact_no' => $admission->primary_contact_no,
            'whatsapp_no' => $admission->whatsapp_no,
            'email' => $admission->email,
            'ledger_group' => $request->ledger_group,
            'opening_balance' => $request->opening_balance,
            'opening_type' => $request->opening_type,
            'gst_no' => $request->gst_no,
            'aadhar_no' => $admission->aadhar_no,
            'permanent_address' => $admission->permanent_address,
            'state' => $admission->permanent_state,
            'city' => $admission->permanent_city,
            'city_town_village' => $admission->permanent_city_town,
            'pin_code' => $admission->permanent_pin_code,
            'temporary_address' => $admission->temporary_address,
        ]);

        if ($request->hasFile('l_docu_uplode') && $request->file('l_docu_uplode')->isValid()) {
            $ledger->addMediaFromRequest('l_docu_uplode')->toMediaCollection('l_docu_uplode');
        }

        if ($request->hasFile('ledger_file')) {
            $ledger->clearMediaCollection('ledger_file');
            foreach ($request->file('ledger_file') as $file) {
                if ($file->isValid()) {
                    $ledger->addMedia($file)->toMediaCollection('ledger_file');
                }
            }
        }

        $admission = $admission->load(['licence', 'branch']);

        return response()->json([
        'success' => true,
        'message' => 'Admission With Lager created successfully',
        'admission' => [
            'id' => $admission->id,
            'student_name' => $admission->student_name,
            'licence' => [
                'licence_no' => $admission->licence->licence_no,
                'licence_name' => $admission->licence->owner_name, // adjust field
            ],
            'branch' => [
                'id' => $admission->branch->id,
                'name' => $admission->branch->branch_name, // adjust field
            ],
            // add rest of fields as needed...
        ]
    ]);

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
    $validator = Validator::make($request->all(),$this->validation());

    if ($validator->fails()) {
        return response()->json(['status' => false, 'message' => $validator->errors()], 200);
    }
        $licence = Licence::where('licence_no', $request->licence_no)->first();
    if (!$licence) {
        return response()->json(['error' => 'Invalid licence.'], 404);
    }

    $branch = Branch::where('id', $request->branch_id)
                    ->where('licence_no', $licence->licence_no)
                    ->first();

    if (!$branch) {
        return response()->json(['error' => 'The selected branch does not belong to the given licence.'], 422);
    }


    try {
        $admission = Admissionform::findOrFail($id);

        // Update non-file fields
        $admission->update($request->only([
            'licence_no',
            'branch_id',
            'ledger_id',
            'admission_date',
            'student_id',
            'student_name',
            'gender',
            'marital_status',
            'age',
            'aadhar_no',
            'caste',
            'primary_contact_no',
            'whatsapp_no',
            'email',
            'college_name',
            'course',
            'date_of_birth',
            'year',
            'father_name',
            'mother_name',
            'guardian',
            'emergency_no',
            'permanent_address',
            'permanent_state',
            'permanent_city',
            'permanent_city_town',
            'permanent_pin_code',
            'temporary_address',
            'temporary_state',
            'temporary_city',
            'temporary_city_town',
            'temporary_pin_code',
        ]));

        // ✅ Delete old image if new image is uploaded
        if ($request->hasFile('image')) {
            $admission->clearMediaCollection('image');
            $admission->addMediaFromRequest('image')->toMediaCollection('image');
        }

        // ✅ Delete old files if new files are uploaded
        if ($request->hasFile('upload_file')) {
            $admission->clearMediaCollection('upload_file');

            foreach ($request->file('upload_file') as $file) {
                if ($file->isValid()) {
                    $admission->addMedia($file)->toMediaCollection('upload_file');
                }
            }
        }

        $ledgerValidator = Validator::make($request->all(), $this->lager());

        if ($ledgerValidator->fails()) {
            return response()->json(['errors' => $ledgerValidator->errors()->first()], 422);
        }
        $ledger = Ledgermaster::where('student_id', $admission->id)->firstOrFail();

        $ledger->update([
            'licence_no' => $admission->licence_no,
            'branch_id' => $admission->branch_id,
            'student_id' => $admission->id,
            'title' => $request->title,
            'ledger_name' => $admission->student_name,
            'relation_type' => $request->relation_type,
            'name' => $admission->father_name,
            'contact_no' => $admission->primary_contact_no,
            'whatsapp_no' => $admission->whatsapp_no,
            'email' => $admission->email,
            'ledger_group' => $request->ledger_group,
            'opening_balance' => $request->opening_balance,
            'opening_type' => $request->opening_type,
            'gst_no' => $request->gst_no,
            'aadhar_no' => $admission->aadhar_no,
            'permanent_address' => $admission->permanent_address,
            'state' => $admission->permanent_state,
            'city' => $admission->permanent_city,
            'city_town_village' => $admission->permanent_city_town,
            'pin_code' => $admission->permanent_pin_code,
            'temporary_address' => $admission->temporary_address,
        ]);
         if ($request->hasFile('ledger_file')) {
                $ledger->clearMediaCollection('ledger_file');
                foreach ($request->file('ledger_file') as $file) {
                    if ($file->isValid()) {
                        $ledger->addMedia($file)->toMediaCollection('ledger_file');
                    }
                }
            }

            if ($request->hasFile('l_docu_uplode')) {
                $ledger->clearMediaCollection('l_docu_uplode');
                $ledger->addMediaFromRequest('l_docu_uplode')->toMediaCollection('l_docu_uplode');
            }

        $admission = $admission->load(['licence', 'branch']);

        return response()->json([
        'success' => true,
        'message' => 'Admission form with Ledger updated successfully',
        'admission' => $admission
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
        $admission = Admissionform::findOrFail($id);

        // Delete ledger media
        $admission->clearMediaCollection('upload_file');
        $admission->clearMediaCollection('image');


        $ledgers = Ledgermaster::where('student_id', $admission->id)->get();

        foreach ($ledgers as $ledger) {
            $ledger->clearMediaCollection('ledger_file');
            $ledger->clearMediaCollection('l_docu_uplode');
            $ledger->delete();
        }

        // Delete the ledger itself
        $admission->delete();

        return response()->json(['status' => true, 'message' => 'admission and associated Ledger  deleted successfully'], 200);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to delete ledger or related admission',
            'message' => $e->getMessage()
        ], 500);
    }
    }
}
