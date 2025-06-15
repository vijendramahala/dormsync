<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ledgermaster;
use App\Models\Licence;
use App\Models\Branch;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


class LedgermasterController extends Controller
{

    public function index()
    {
        $licenceno = Auth::user()->licence_no;
        $branchid = Auth::user()->branch_id;

        $ledger = Ledgermaster::with([
            'licence:id,licence_no',
            'branch:id,branch_name,b_city'
        ])
        ->where('licence_no', $licenceno)
        ->where('branch_id', $branchid)
        ->get();

        return response()->json([
            'status' => true,
            'data' => $ledger
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
        return [
        'licence_no' => 'required|string|exists:licences,licence_no',
        'branch_id' => 'required|integer|exists:branches,id',
        'title' => 'required|string|max:100',
        'ledger_name' => 'required|string|max:255',
        'relation_type' => 'required|in:S/O,D/O,W/O',
        'ledger_file' => 'nullable|array',
        'ledger_file.*' => 'file|max:2048',
        'name' => 'nullable|string|max:255',
        'contact_no' => 'nullable|string|regex:/^[0-9]{10}$/',
        'whatsapp_no' => 'nullable|string|regex:/^[0-9]{10}$/',
        'email' => 'nullable|email|max:255',
        'ledger_group' => 'required|string|max:255',
        'opening_balance' => 'required|numeric|min:0',
        'opening_type' => 'required|in:Cr,Dr',
        'gst_no' => 'nullable|string|max:15',
        'aadhar_no' => 'nullable|string|digits:12',
        'l_docu_uplode' => 'nullable|file|max:2048',
        'permanent_address' => 'nullable|string|max:500',
        'state' => 'required|string|max:100',
        'city' => 'required|string|max:100',
        'city_town_village' => 'nullable|string|max:100',
        'pin_code' => 'nullable|string|digits:6',
        'temporary_address' => 'nullable|string|max:500',
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
  public function store(Request $request)
    {
        // STEP 1: Ledger Validation
        $ledgerValidator = Validator::make($request->all(),$this->validation());

        if ($ledgerValidator->fails()) {
            return response()->json(['status' => false, 'message' => $ledgerValidator->errors()->first()], 200);
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
                $ledger = $ledger->load(['licence', 'branch']);


            return response()->json([
                'status' => true,
                'message' => 'Ledger saved successfully',
                'data' => [
                    'ledger' => $ledger,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

  public function update(Request $request, string $id)
    {
        // STEP 1: Validate Ledger Data
    $ledgerValidator = Validator::make($request->all(),$this->validation());

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

            $ledger = $ledger->load(['licence', 'branch']);

            return response()->json([
                'success' => true,
                'message' => 'Ledger  updated successfully',
                'data' => [
                    'ledger' => $ledger->load(['licence', 'branch']),
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

        $ledger->delete();

        return response()->json(['message' => 'Ledger  deleted successfully'], 200);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to delete ledger',
            'message' => $e->getMessage()
        ], 500);
    }
}

}
