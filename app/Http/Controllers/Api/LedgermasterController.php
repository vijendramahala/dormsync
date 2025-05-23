<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ledgermaster;
use App\Models\Admissionform;
use App\Models\Licence;
use App\Models\Branch;
use Illuminate\Support\Facades\Validator;


class LedgermasterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ledger = Ledgermaster::with(['licence', 'branch'])->get();

    return response()->json([
        'status' => true,
        'data' => $ledger
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
    // STEP 1: Ledger Validation
    $ledgerValidator = Validator::make($request->all(), [
        'licence_no' => 'required|string|exists:licences,licence_no',
        'branch_id' => 'required|integer|exists:branches,id',
        'title' => 'required|string|max:100',
        'ledger_name' => 'required|string|max:255',
        'relation_type' => 'required|in:S/O,D/O,W/O',
        'ledger_file' => 'nullable|array',
        'ledger_file.*' => 'file|max:2048',
        'name' => 'required|string|max:255',
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
        'state' => 'required|string|max:100',
        'city' => 'required|string|max:100',
        'city_town_village' => 'nullable|string|max:100',
        'address' => 'nullable|string|max:500',
        'pin_code' => 'nullable|string|digits:6',
        'temporary_address' => 'nullable|string|max:500',
    ]);

    if ($ledgerValidator->fails()) {
        return response()->json(['errors' => $ledgerValidator->errors()->first()], 422);
    }

   // ✅ STEP 1.1: Licence and Branch Belonging Check
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
        // STEP 2: Create Ledger
        $ledger = Ledgermaster::create([
            'licence_no' => $request->licence_no,
            'branch_id' => $request->branch_id,
            'title' => $request->title,
            'ledger_name' => $request->ledger_name,
            'relation_type' => $request->relation_type,
            'name' => $request->name,
            'contact_no' => $request->contact_no,
            'whatsapp_no' => $request->whatsapp_no,
            'email' => $request->email,
            'ledger_group' => $request->ledger_group,
            'opening_balance' => $request->opening_balance,
            'opening_type' => $request->opening_type,
            'gst_no' => $request->gst_no,
            'aadhar_no' => $request->aadhar_no,
            'permanent_address' => $request->permanent_address,
            'state' => $request->state,
            'city' => $request->city,
            'city_town_village' => $request->city_town_village,
            'address' => $request->address,
            'pin_code' => $request->pin_code,
            'temporary_address' => $request->temporary_address,
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

        // STEP 3: Admission Validation
        $admissionValidator = Validator::make($request->all(), [
            // 'ledger_id' => 'required|integer|exists:ledgermaster,id',
            'admission_date' => 'required|date',
            'student_id' => 'required|string|max:50',
            'student_name' => 'required|string|max:255',
            'gender' => 'required|string|in:male,female,other',
            'marital_status' => 'required|string|in:single,married,divorced,widowed',
            'aadhar_no' => 'required|string|size:12',
            'upload_file' => 'nullable|array',
            'upload_file.*' => 'file|max:2048',
            'image' => 'nullable|file|max:2048',
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
        ]);

        if ($admissionValidator->fails()) {
            return response()->json(['errors' => $admissionValidator->errors()], 422);
        }

        // STEP 4: Create Admission
        $admission = Admissionform::create([
            'licence_no' => $ledger->licence_no,
            'branch_id' => $ledger->branch_id,
            'ledger_id' => $ledger->id,
            'admission_date' => $request->admission_date,
            'student_id' => $request->student_id,
            'student_name' => $request->student_name,
            'image' => $request->image,
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

        $ledger = $ledger->load(['licence', 'branch']);

        return response()->json([
            'message' => 'Ledger and admission saved successfully',
            'data' => [
                'ledger' => $ledger,
                'admission' => $admission
            ]
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Something went wrong',
            'message' => $e->getMessage(),
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
        // STEP 1: Validate Ledger Data
        $ledgerValidator = Validator::make($request->all(), [
            'licence_no' => 'required|string|exists:licences,licence_no',
            'branch_id' => 'required|integer|exists:branches,id',
            'title' => 'required|string|max:100',
            'ledger_name' => 'required|string|max:255',
            'relation_type' => 'required|in:S/O,D/O,W/O',
            'name' => 'required|string|max:255',
            'contact_no' => 'nullable|string|regex:/^[0-9]{10}$/',
            'whatsapp_no' => 'nullable|string|regex:/^[0-9]{10}$/',
            'email' => 'nullable|email|max:255',
            'ledger_file' => 'nullable|array',
            'ledger_file.*' => 'file|max:2048',
            'ledger_group' => 'required|string|max:255',
            'opening_balance' => 'required|numeric|min:0',
            'opening_type' => 'required|in:cr,br',
            'gst_no' => 'nullable|string|max:15',
            'aadhar_no' => 'nullable|string|digits:12',
            'l_docu_uplode' => 'nullable|file|max:2048',
            'permanent_address' => 'nullable|string|max:500',
            'state' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'city_town_village' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'pin_code' => 'nullable|string|digits:6',
            'temporary_address' => 'nullable|string|max:500',
        ]);

        if ($ledgerValidator->fails()) {
            return response()->json(['errors' => $ledgerValidator->errors()->first()], 422);
        }

        // STEP 1.1: Licence and Branch Validation
        $licence = Licence::where('licence_no', $request->licence_no)->first();
        $branch = Branch::where('id', $request->branch_id)->where('licence_no', $licence->licence_no)->first();

        if (!$licence || !$branch) {
            return response()->json(['error' => 'Invalid licence or branch.'], 422);
        }

        try {
            $ledger = Ledgermaster::findOrFail($id);

            $ledger->update($request->only([
                'licence_no',
                'branch_id',
                'title',
                'ledger_name',
                'relation_type',
                'name',
                'contact_no',
                'whatsapp_no',
                'email',
                'ledger_group',
                'opening_balance',
                'opening_type',
                'gst_no',
                'aadhar_no',
                'permanent_address',
                'state',
                'city',
                'city_town_village',
                'address',
                'pin_code',
                'temporary_address',
            ]));

            // Handle ledger file uploads
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

            // STEP 2: Admission Validation
            $admissionValidator = Validator::make($request->all(), [
                'admission_date' => 'required|date',
                'student_id' => 'required|string|max:50',
                'student_name' => 'required|string|max:255',
                'gender' => 'required|string|in:male,female,other',
                'marital_status' => 'required|string|in:single,married,divorced,widowed',
                'aadhar_no' => 'required|string|size:12',
                'upload_file' => 'nullable|array',
                'upload_file.*' => 'file|max:2048',
                'image' => 'nullable|file|mimes:jpeg,png|max:2048',
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
            ]);

            if ($admissionValidator->fails()) {
                return response()->json(['errors' => $admissionValidator->errors()], 422);
            }

            $admission = Admissionform::where('ledger_id', $ledger->id)->firstOrFail();

            $admission->update($request->only([
                'admission_date',
                'student_id',
                'student_name',
                'gender',
                'marital_status',
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

            if ($request->hasFile('image')) {
                $admission->clearMediaCollection('image');
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

            return response()->json([
                'success' => true,
                'message' => 'Ledger and admission updated successfully',
                'data' => [
                    'ledger' => $ledger->load(['licence', 'branch']),
                    'admission' => $admission
                ]
            ]);

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
        $ledger = Ledgermaster::findOrFail($id);

        // Delete ledger media
        $ledger->clearMediaCollection('ledger_file');
        $ledger->clearMediaCollection('l_docu_uplode');


        $admissions = Admissionform::where('ledger_id', $ledger->id)->get();

        foreach ($admissions as $admission) {
            $admission->clearMediaCollection('upload_file');
            $admission->clearMediaCollection('image');
            $admission->delete();
        }

        // Delete the ledger itself
        $ledger->delete();

        return response()->json(['message' => 'Ledger and associated admission(s) deleted successfully'], 200);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to delete ledger or related admission',
            'message' => $e->getMessage()
        ], 500);
    }
}

}
