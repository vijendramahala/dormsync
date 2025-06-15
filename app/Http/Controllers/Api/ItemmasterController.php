<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Itemmaster;
use App\Models\Licence;
use App\Models\Branch;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ItemmasterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
           $licenceno = Auth::user()->licence_no;
           $branchid = Auth::user()->branch_id;

           $item = Itemmaster::with([
            'licence:id,licence_no',
            'branch:id,branch_name,b_city'
           ])
           ->where('licence_no', $licenceno)
           ->where('branch_id', $branchid)
           ->get();

           return response()->json([
            'status' => true,
            'data' => $item
        ], 200);
     }

    private function validation()
    {
        return [
            'licence_no' => 'nullable|exists:licences,licence_no',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'item_no' => 'required|string|max:255',
            'item_name' => 'required|string|max:255',
            'item_group' => 'required|string|max:255',
            'manufacturer' => 'required|string|max:255',
            'stock_qty' => 'required|integer|min:0',
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),$this->validation());

        if($validator->fails()){
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);
        }
        $licence = Licence::where('licence_no', $request->licence_no)->first();
        if (!$licence) {
            return response()->json(['error' => 'Invalid licence number.'], 404);
        }

        // Step 2: Check if branch belongs to this licence
        $branch = Branch::where('id', $request->branch_id)
                        ->where('licence_no', $request->licence_no)
                        ->first();
        if (!$branch) {
            return response()->json([
                'error' => 'The selected branch does not belong to the provided licence_no.'
            ], 422);
        }

        try{
            $item = Itemmaster::create([
                'licence_no' => $request->licence_no,
                'branch_id' => $request->branch_id,
                'item_no' => $request->item_no,
                'item_name' => $request->item_name,
                'item_group' => $request->item_group,
                'manufacturer' => $request->manufacturer,
                'stock_qty' => $request->stock_qty
            ]);

                $item = $item->load(['licence', 'branch']);

                return response()->json([
                    'status' => true,
                'message' => 'Item Master successfully',
                'data' => $item
            ], 200);

        } catch (\Excaption $e){
            return response()->json([
                'success' => true,
                'mwssage' => 'Somthing want wrong',
                'error' => $e->getmessage()
            ],500);
        }
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(),$this->validation());

        if($validator->fails()){
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);
        }
        $licence = Licence::where('licence_no', $request->licence_no)->first();
        if (!$licence) {
            return response()->json(['error' => 'Invalid licence number.'], 404);
        }

        // Step 2: Check if branch belongs to this licence
        $branch = Branch::where('id', $request->branch_id)
                        ->where('licence_no', $request->licence_no)
                        ->first();
        if (!$branch) {
            return response()->json([
                'error' => 'The selected branch does not belong to the provided licence_no.'
            ], 422);
        }
        try{
            $item = Itemmaster::findorFail($id);

            $item->update([
                'licence_no' => $request->licence_no,
                'branch_id' => $request->branch_id,
                'item_no' => $request->item_no,
                'item_name' => $request->item_name,
                'item_group' => $request->item_group,
                'manufacturer' => $request->manufacturer,
                'stock_qty' => $request->stock_qty
            ]);

                $item = $item->load(['licence', 'branch']);

                return response()->json([
                    'status' => true,
                'message' => 'Item Master update1 successfully',
                'data' => $item
            ], 200);
        } catch (\Excaptio $e){
            return response()->json([
                'sucess' => false,
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
            $item = Itemmaster::findorFail($id);

            $item->delete();

            return response()->json(['status' => true, 'message' => 'deleted successfully'],200);
        } catch (\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Somthing want wrong',
                'error' => $e->getmessage()
            ],500);
        }
    }
}
