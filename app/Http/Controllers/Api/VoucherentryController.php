<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Voucherentry;
use App\Models\Licence;
use App\Models\Branch;
use Illuminate\Support\Facades\Validator;

class VoucherentryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $voucher = Voucherentry::with(['licence', 'branch'])->get();

     return response()->json([
        'status' => true,
        'data' => $voucher
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
    private function validation(){
        return [
            'licence_no' => 'nullable|string|max:50',
            'branch_id' => 'required|exists:branches,id',
            'voucher_type' => 'required|string|max:50',
            'voucher_date' => 'required|date',
            'voucher_no' => 'required|string|max:50',
            // 'voucher_no' => 'required|string|max:50|unique:vouchers,voucher_no',
            'payment_mode' => 'required|string|max:30',
            'payment_balance' => 'required|numeric|min:0',
            'account_head' => 'required|string|max:100',
            'account_balance' => 'required|numeric|min:0',
            'debit' => 'required|numeric|min:0',
            'credit' => 'required|numeric|min:0',
            'narration' => 'required|string|max:255',
            'paid_by' => 'required|string|max:100',
            'remark' => 'nullable|string|max:255',
        ];
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),$this->validation());

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
                $voucher = Voucherentry::create([
                    'licence_no' => $request->licence_no,
                    'branch_id' => $request->branch_id,
                    'voucher_type' => $request->voucher_type,
                    'voucher_date' => $request->voucher_date,
                    'voucher_no' => $request->voucher_no,
                    'payment_mode' => $request->payment_mode,
                    'payment_balance' => $request->payment_balance,
                    'account_head' => $request->account_head,
                    'account_balance' => $request->account_balance,
                    'debit' => $request->debit,
                    'credit' => $request->credit,
                    'narration' => $request->narration,
                    'paid_by' => $request->paid_by,
                    'remark' => $request->remark

                ]);
                if ($request->hasFile('document') && $request->file('document')->isValid()) {
                    $voucher->addMediaFromRequest('document')->toMediaCollection('document');
                }
                
                $voucher = $voucher->load(['licence', 'branch']);

                return response()->json([
                'message' => 'Voucher Entry added successfully',
                'data' => $voucher
                ], 201);

            }catch (\Exception $e){
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
        $validator = Validator::make($request->all(),$this->validation());

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
                $voucher = Voucherentry::findorFail($id);
                $voucher->update([
                    'licence_no' => $request->licence_no,
                    'branch_id' => $request->branch_id,
                    'voucher_type' => $request->voucher_type,
                    'voucher_date' => $request->voucher_date,
                    'voucher_no' => $request->voucher_no,
                    'payment_mode' => $request->payment_mode,
                    'payment_balance' => $request->payment_balance,
                    'account_head' => $request->account_head,
                    'account_balance' => $request->account_balance,
                    'debit' => $request->debit,
                    'credit' => $request->credit,
                    'narration' => $request->narration,
                    'paid_by' => $request->paid_by,
                    'remark' => $request->remark
                ]);
                if ($request->hasFile('document') && $request->file('document')->isValid()) {
                    $voucher->clearMediaCollection('document');
                $voucher->addMediaFromRequest('document')->toMediaCollection('document');
                }

                $voucher = $voucher->load(['licence', 'branch']);

                return response()->json([
                    'success' => true,
                    'message' => 'Voucher Entry update successsfully',
                    'data' => $voucher
                ]);
            } catch (\Exception $e){
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
            $voucher = Voucherentry::findorFail($id);

            $voucher->clearMediaCollection('document');

            $voucher->delete();

            return response()->json([
                'message' => 'Voucher Entry deleted successfully'
            ],200);
        } catch (\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Somthing want wrong',
                'error' => $e->getmessage()
            ],500);
        }
    }
}
