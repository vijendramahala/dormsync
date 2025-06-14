<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Licence;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    private function validationrule(){

        return [
            'licence_no' => 'required|exists:licences,licence_no',
            'branch_id' => 'required|exists:branches,id',
            'u_name' => 'required',
            'username' => 'required',
            'password' => 'required|min:6'
        ];
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->validationrule());

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 200);
        }
        try{
            $user = User::create([
                'licence_no' => $request->licence_no,
                'branch_id' => $request->branch_id,
                'u_name' => $request->u_name,
                'username' => $request->username,
                'password' => $request->password,
                'role' => 'subadmin'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'subadmin created successfully',
                'data' => $user
            ], 200);
        } catch (\Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'somthing want wrong',
                'error' => $e->getmessage()
            ], 500);
        }
    
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), $this->validationrule());

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 200);
        }
        try{
            $user = User::findorFail($id);

            $user->update([
                'licence_no' => $request->licence_no,
                'branch_id' => $request->branch_id,
                'u_name' => $request->u_name,
                'username' => $request->username,
                'password' => $request->password,
                'role' => 'subadmin'
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Subadmin updated successfully',
                'data' => $user
            ], 200);
        } catch (\Exceptino $e){
            return response()->json([
                'status' => false,
                'message' => 'somthing want wrong',
                'error' => $e->getmessage()
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try{
            $user = User::findorFail($id);

            $user->delete();

            return response()->json([
                'status' => true,
                'message' => 'Subadmin deleted successfully'
            ], 200);
        } catch (\Exceptino $e){
            return response()->json([
                'status' => false,
                'message' => 'somthing want wrong',
                'error' => $e->getmessage()
            ], 500);
        }
    }
}
