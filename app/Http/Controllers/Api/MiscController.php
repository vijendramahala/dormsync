<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Misc;
use App\Models\Licence;
use Illuminate\Support\Facades\Validator;

class MiscController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'licence_no' => 'required|exists:licences,licence_no',
            'misc_id' => 'required|integer',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 200);
        }

        $misc = Misc::where('licence_no', $request->licence_no)
                        ->Where('misc_id', $request->misc_id)
                        ->get();

        return response()->json([
            'status' => true,
            'data' => $misc
        ], 200);
    } 
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'licence_no' => 'required|exists:licences,licence_no',
           'misc_id' => 'required|integer',
           'name' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 200);
        }

        try{
        $misc = Misc::create([
            'licence_no' => $request->licence_no,
            'misc_id' => $request->misc_id,
            'name' => $request->name
        ]);
        return response()->json([
            'status' => true,
            'message' => 'Misc created successfully',
            'data' => $misc
        ], 200);

        } catch (\Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Somthing want wrong',
                'error' => $e->getmessage()
            ], 500);
        }
    }
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
           'licence_no' => 'required|exists:licences,licence_no',
           'misc_id' => 'required|integer',
           'name' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 200);
        }

        try{
            $misc = Misc::findorFail($id);

            $misc->update([
                'licence_no' => $request->licence_no,
            'misc_id' => $request->misc_id,
            'name' => $request->name
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Misc updated successfully',
                'data' => $misc
            ], 200);
        } catch (\Eception $e){
            return response()->json([
                'status' => false,
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
            $misc = Misc::findorFail($id);

            $misc->delete();

            return response()->json([
                'status' => true,
                'message' => 'Misc delete successfully',
            ], 200);
        } catch (\Exception $e){
            return response()->json([
                'status' => false,
                'messahe' => 'Somthing want Wrong',
                'error' => $e->getmessage()
            ], 500);
        }
    }
}
