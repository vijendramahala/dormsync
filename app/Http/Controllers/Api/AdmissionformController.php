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
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
            'licence:id,licence_no',
            'branch:id,branch_name,b_city',
            'ledger:student_id,opening_balance,opening_type'
        ])
        ->where('licence_no', $licenceno)
        ->where('branch_id', $branchid)
        ->get();

        return response()->json([
            'status' => true,
            'data' => $admission
        ], 200);
    }

   public function activstatus(Request $request)
    {
        try {
            $licenceno = Auth::user()->licence_no;
            $branchid = Auth::user()->branch_id;

            $admissionQuery = Admissionform::with([
                'licence:id,licence_no',
                'branch:id,branch_name,b_city',
                'ledger:student_id,opening_balance,opening_type'
            ])
            ->where('licence_no', $licenceno)
            ->where('branch_id', $branchid)
            ->where('active_status', 1);

            // 📅 From Date Filter
            if ($request->filled('from_date')) {
                $fromDate = Carbon::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
                $admissionQuery->whereRaw("STR_TO_DATE(admission_date, '%d/%m/%Y') >= ?", [$fromDate]);
            }

            // 📅 To Date Filter
            if ($request->filled('to_date')) {
                $toDate = Carbon::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
                $admissionQuery->whereRaw("STR_TO_DATE(admission_date, '%d/%m/%Y') <= ?", [$toDate]);
            }

            $admissions = $admissionQuery->get();

            return response()->json([
                'status' => true,
                'data' => $admissions
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    private function validation(){

        return [
       'licence_no' => 'required|exists:licences,licence_no',
        'branch_id' => 'required|exists:branches,id',
        'admission_date' => 'required|date_format:d/m/Y',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'student_id' => 'nullable|string|max:50',
        'student_name' => 'required|unique:admissionforms,student_name',
        'gender' => 'required|string|in:Male,Female,Other',
        'marital_status' => 'required|string|in:Single,Married',
        'aadhar_no' => 'required|string|size:12',
        'upload_file' => 'nullable|array',
        'upload_file.*' => 'file|max:2048',
        'caste' => 'required|string|in:OBC,General,SC,ST',
        'primary_contact_no' => 'required|string|regex:/^[0-9]{10}$/',
        'whatsapp_no' => 'nullable|string|regex:/^[0-9]{10}$/',
        'email' => 'nullable|email|max:255',
        'college_name' => 'nullable|string|max:255',
        'course' => 'nullable|string|max:100',
        'date_of_birth' => 'required|date_format:d/m/Y',
        'year' => 'nullable|string|max:20',
        'father_name' => 'required|string|max:255',
        'mother_name' => 'required|string|max:255',
        'parent_contect' => 'required|string|regex:/^[0-9]{10}$/',
        'guardian' => 'nullable|string|max:255',
        'emergency_no' => 'nullable|string|regex:/^[0-9]{10}$/',
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
        'other1' => 'nullable|string|max:255',
        'other2' => 'nullable|string|max:255',
        'other3' => 'nullable|string|max:255',
        'other4' => 'nullable|string|max:255',
        'other5' => 'nullable|string|max:255',
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
            'ledger_group' => 'required|string|in:Sundry Debtor,max:255',
            'opening_balance' => 'required|numeric|min:0',
            'opening_type' => 'required|in:Cr,Dr',
            'gst_no' => 'nullable|string|max:15',
            'aadhar_no' => 'nullable|string|digits:12',
            'l_docu_uplode' => 'nullable|file|max:2048',
            'permanent_address' => 'nullable|string|max:500',
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
        DB::beginTransaction(); // Start transaction

        try {
            $inputStudentId = $request->student_id;

        if ($inputStudentId) {
        
            if (Admissionform::where('student_id', $inputStudentId)->exists()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Student ID already exists.',
                ], 422);
            }
            $studentId = $inputStudentId;
        } else {
            
            $maxIdRaw = Admissionform::selectRaw("MAX(CAST(SUBSTRING_INDEX(student_id, '-', -1) AS UNSIGNED)) as max_number")->value('max_number');
            $nextNumber = $maxIdRaw ? $maxIdRaw + 1 : 1;
            $studentId = 'STU-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }


            // Step 1: Validate Admission form
            $validator = Validator::make($request->all(), $this->validation());
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);
            }
            // Step 2: Licence and Branch checks
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

            // Step 3: Create Admission
            $admission = Admissionform::create([
                'licence_no' => $request->licence_no,
                'branch_id' => $request->branch_id,
                'ledger_id' => $request->ledger_id,
                'admission_date' => $request->admission_date,
                'student_id' => $studentId,
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
                'parent_contect' => $request->parent_contect,
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
                'other1' => $request->other1,
                'other2' => $request->other2,
                'other3' => $request->other3,
                'other4' => $request->other4,
                'other5' => $request->other5,
            ]);

            // Step 4: Handle admission image and files
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $admission->addMediaFromRequest('image')->toMediaCollection('image');
            }

            if ($request->hasFile('upload_file')) {
                foreach ($request->file('upload_file') as $file) {
                    if ($file->isValid()) {
                        $admission->addMedia($file)->toMediaCollection('upload_file');
                    }
                }
            }

            // Step 5: Validate Ledger
            $ledgerValidator = Validator::make($request->all(), $this->lager());
            if ($ledgerValidator->fails()) {
                DB::rollBack(); // Rollback admission if ledger validation fails
                return response()->json(['status' => false, 'message' => $ledgerValidator->errors()->first()], 200);
            }

            // Step 6: Create Ledger
            $ledger = Ledgermaster::create([
                'licence_no' => $admission->licence_no,
                'branch_id' => $admission->branch_id,
                'student_id' => $admission->student_id,
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
                'other1' => $request->other1,
                'other2' => $request->other2,
                'other3' => $request->other3,
                'other4' => $request->other4,
                'other5' => $request->other5,
            ]);

            // Step 7: Upload ledger files
            if ($request->hasFile('l_docu_uplode') && $request->file('l_docu_uplode')->isValid()) {
                $ledger->addMediaFromRequest('l_docu_uplode')->toMediaCollection('l_docu_uplode');
            }

            if ($request->hasFile('ledger_file')) {
                foreach ($request->file('ledger_file') as $file) {
                    if ($file->isValid()) {
                        $ledger->addMedia($file)->toMediaCollection('ledger_file');
                    }
                }
            }

            DB::commit(); // All operations successful

            $admission->load(['licence', 'branch']);

            return response()->json([
                'status' => true,
                'message' => 'Admission with Ledger created successfully.',
                'admission' => [
                    'id' => $admission->id,
                    'student_name' => $admission->student_name,
                    'licence' => [
                        'licence_no' => $admission->licence->licence_no,
                        'licence_name' => $admission->licence->owner_name,
                    ],
                    'branch' => [
                        'id' => $admission->branch->id,
                        'name' => $admission->branch->branch_name,
                    ],
                    // Add any other required fields...
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack(); // Something failed
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   public function update(Request $request, string $id)
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
        'licence_no' => 'required|exists:licences,licence_no',
        'branch_id' => 'required|exists:branches,id',
        'admission_date' => 'required|date_format:d/m/Y',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'student_id' => 'nullable|string|max:50|unique:admissionforms,student_id,' . $id,
        'gender' => 'required|string|in:Male,Female,Other',
        'marital_status' => 'required|string|in:Single,Married',
        'aadhar_no' => 'required|string|size:12',
        'upload_file' => 'nullable|array',
        'upload_file.*' => 'file|max:2048',
        'caste' => 'required|string|in:OBC,General,SC,ST',
        'primary_contact_no' => 'required|string|regex:/^[0-9]{10}$/',
        'whatsapp_no' => 'nullable|string|regex:/^[0-9]{10}$/',
        'email' => 'nullable|email|max:255',
        'college_name' => 'nullable|string|max:255',
        'course' => 'nullable|string|max:100',
        'date_of_birth' => 'required|date_format:d/m/Y',
        'year' => 'nullable|string|max:20',
        'father_name' => 'required|string|max:255',
        'mother_name' => 'required|string|max:255',
        'parent_contect' => 'required|string|regex:/^[0-9]{10}$/',
        'guardian' => 'nullable|string|max:255',
        'emergency_no' => 'nullable|string|regex:/^[0-9]{10}$/',
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
        'other1' => 'nullable|string|max:255',
        'other2' => 'nullable|string|max:255',
        'other3' => 'nullable|string|max:255',
        'other4' => 'nullable|string|max:255',
        'other5' => 'nullable|string|max:255',
        ]);

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

             // 🔽 student_id generate ya use manual
        if (!$request->filled('student_id')) {
            $lastId = Admissionform::max('id') ?? 0;
            $nextId = $lastId + 1;
            $studentId = 'STU-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
        } else {
            $studentId = $request->student_id;
        }

            // Update non-file fields
            $admission->update(array_merge(
            $request->only([
                'licence_no',
                'branch_id',
                'ledger_id',
                'admission_date',
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
                'parent_contect',
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
                'other1',
                'other2',
                'other3',
                'other4',
                'other5',
            ]),
            ['student_id' => $studentId]
        ));


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
                DB::rollback();
                return response()->json(['status' => false, 'message' => $ledgerValidator->errors()->first()], 200);
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
                'other1' => $request->other1,
                'other2' => $request->other2,
                'other3' => $request->other3,
                'other4' => $request->other4,
                'other5' => $request->other5,
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

                DB::commit();

            $admission = $admission->load(['licence', 'branch']);

            return response()->json([
            'status' => true,
            'message' => 'Admission form with Ledger updated successfully',
            'admission' => $admission
        ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

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
                'status' => false,
                'error' => 'Failed to delete ledger or related admission',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}