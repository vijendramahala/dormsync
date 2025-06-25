<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Prospect;
use App\Models\Licence;
use App\Models\Branch;
use App\Models\ProspectHistory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProspectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
    {
        $licenceno = Auth::user()->licence_no;
        $branchid = Auth::user()->branch_id;

        $prospect = Prospect::with([
            'licence:id,licence_no',
            'branch:id,branch_name,b_city'
        ])
        ->where('licence_no', $licenceno)
        ->where('branch_id', $branchid)
        ->orderBy('next_appointment_date', 'asc') // 📅 earliest date first
        ->orderBy('time', 'desc') // 🕙 latest time first (for same date)
        ->get();

        return response()->json([
            'status' => true,
            'data' => $prospect
        ], 200);
    }


    private function prospectValidationRules()
    {
        return [
            'licence_no' => 'required|exists:licences,licence_no',
            'branch_id' => 'required|exists:branches,id',
            'student_name' => 'required|unique:prospects,student_name',
            'gender' => 'required|in:Male,Female,Other',
            'contact_no' => 'required|string|max:15',
            'father_name' => 'nullable|string|max:255',
            'f_contact_no' => 'nullable|string|max:15',
            'address' => 'required|string|max:500',
            'staff' => 'required|string|max:255',
            'next_appointment_date' => 'required|date_format:d/m/Y|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'prospect_status' => 'required|string|in:In Process,Admitted,Lost',
            'remark' => 'required|string|max:1000',
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
        $validator = Validator::make($request->all(), $this->prospectValidationRules());

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);
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
                    'father_name' => $request->father_name,
                    'f_contact_no' => $request->f_contact_no,
                    'address' => $request->address,
                    'staff' => $request->staff,
                    'next_appointment_date' => $request->next_appointment_date,
                    'time' => $request->time,
                    'city' => $request->city,
                    'state' => $request->state,
                    'prospect_status' => $request->prospect_status,
                    'remark' => $request->remark,
                    'other1' => $request->other1,
                    'other2' => $request->other2,
                    'other3' => $request->other3,
                    'other4' => $request->other4,
                    'other5' => $request->other5,
                ]);
                $prospect = $prospect->load(['licence', 'branch']);

                return response()->json([
                'status' => true,
                'message' => 'prospect added successfully',
                'data' => $prospect
            ], 200);

            } catch (\Exception $e){
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong',
                    'error' => $e->getmessage()
                ], 500);
            }
    }

    public function update(Request $request, string $id)
    {
         $validator = Validator::make($request->all(), [
            'licence_no' => 'required|exists:licences,licence_no',
            'branch_id' => 'required|exists:branches,id',
            'student_name' => 'required',
            'gender' => 'required|in:Male,Female,Other',
            'contact_no' => 'required|string|max:15',
            'father_name' => 'nullable|string|max:255',
            'f_contact_no' => 'nullable|string|max:15',
            'address' => 'required|string|max:500',
            'staff' => 'required|string|max:255',
            'next_appointment_date' => 'required|date_fromat:d/m/Y|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'prospect_status' => 'required|string|in:In Process,Admitted,Lost',
            'remark' => 'required|string|max:1000',
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
                    'father_name' => $request->father_name,
                    'f_contact_no' => $request->f_contact_no,
                    'address' => $request->address,
                    'staff' => $request->staff,
                    'next_appointment_date' => $request->next_appointment_date,
                    'time' => $request->time,
                    'city' => $request->city,
                    'state' => $request->state,
                    'prospect_status' => $request->prospect_status,
                    'remark' => $request->remark,
                    'other1' => $request->other1,
                    'other2' => $request->other2,
                    'other3' => $request->other3,
                    'other4' => $request->other4,
                    'other5' => $request->other5,
                ]);
                $prospect = $prospect->load(['licence', 'branch']);

                return response()->json([
                    'status' => true,
                    'message' => 'prospect update successsfully',
                    'data' => $prospect
                ], 200);

            } catch (\Exception $e) {
                return response()->json([
                'success' => false,
                'message' => 'Somthing want wrong',
                'error' => $e->getmessage()
                ], 500);
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
                'status' => true,
                'message' => 'prospect deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Faild to delete prospect',
                'error' => $e->getmessage()
            ], 500);

        }
    }

    // use Carbon\Carbon; // Add this at the top

    public function report(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'licence_no' => 'required|exists:licences,licence_no',
            'branch_id' => 'required|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 200);
        }

        // 🔄 Convert to start of day and end of day using Carbon
        $from = Carbon::parse($request->from_date)->startOfDay();
        $to = Carbon::parse($request->to_date)->endOfDay();

        $data = Prospect::where('licence_no', $request->licence_no)
            ->where('branch_id', $request->branch_id)
            ->whereBetween('next_appointment_date', [$from, $to])
            ->orderBy('next_appointment_date', 'asc')
            ->orderBy('time', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $data
        ], 200);
    }



    public function createdAtReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'licence_no' => 'required|exists:licences,licence_no',
            'branch_id' => 'required|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 200);
        }

        $from = Carbon::parse($request->from_date)->startOfDay();
        $to = Carbon::parse($request->to_date)->endOfDay();

        $data = Prospect::where('licence_no', $request->licence_no)
            ->where('branch_id', $request->branch_id)
            ->whereBetween('created_at', [$from, $to
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $data
        ], 200);
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
            ], 200);
        }

    public function followUp(Request $request, $id)
    {
        $request->validate([
            'prospect_status' => 'required|string|in:In Process,Admitted,Lost',
        ],);

        $prospect = Prospect::findOrFail($id);
        $oldData = $prospect->toArray(); // old data before update

        $prospect->update([
            'student_name' => $request->student_name,
            'gender' => $request->gender,
            'contact_no' => $request->contact_no,
            'father_name' => $request->father_name,
            'f_contact_no' => $request->f_contact_no,
            'address' => $request->address,
            'staff' => $request->staff,
            'next_appointment_date' => $request->next_appointment_date,
            'time' => $request->time,
            'city' => $request->city,
            'state' => $request->state,
            'prospect_status' => $request->prospect_status,
            'remark' => $request->remark
        ]);

        // ✅ Save history only in follow-up
            ProspectHistory::create([
            'prospect_id' => $prospect->id,
            'updated_by' => auth()->id(),
            'old_data' => $oldData,
            'prospect_status' => $prospect->prospect_status
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Follow-up updated and history saved.',
            'data' => $prospect
        ]);
    }

    public function getProspectHistory($id)
    {
        $userLicence = Auth::user()->licence_no;
        $userBranch = Auth::user()->branch_id;

        
        $prospect = Prospect::where('id', $id)
                    ->where('licence_no', $userLicence)
                    ->where('branch_id', $userBranch)
                    ->first();

        if (!$prospect) {
            return response()->json([
                'status' => false,
                'message' => 'Prospect not found or unauthorized access.'
            ], 200);
        }

        
        $history = ProspectHistory::where('prospect_id', $id)
                    ->with('user:id,username')
                    ->orderBy('created_at', 'desc')
                    ->get();

        if ($history->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No history found for this prospect.'
            ], 200);
        }

        return response()->json([
            'status' => true,
            'data' => $history
        ], 200);
    }

}