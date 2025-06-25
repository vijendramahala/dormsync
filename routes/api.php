<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\LicenceController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\StaffmasterController;
use App\Http\Controllers\Api\CompanydetailCntroller;
use App\Http\Controllers\Api\AdmissionformController;
use App\Http\Controllers\Api\LedgermasterController;
use App\Http\Controllers\Api\UplodeprofileController;
use App\Http\Controllers\Api\BuildingController;
use App\Http\Controllers\Api\FloorController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\VisitorController;
use App\Http\Controllers\Api\LeaveapplicationController;
use App\Http\Controllers\Api\RoomassignController;
use App\Http\Controllers\Api\ProspectController;
use App\Http\Controllers\Api\FeesentryController;
use App\Http\Controllers\Api\VoucherentryController;
use App\Http\Controllers\Api\ItemmasterController;
use App\Http\Controllers\Api\MiscController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\TryController;

// Public Routes
Route::post('/login', [AuthController::class, 'superAdminLogin']);
Route::post('/superadmin', [BranchController::class, 'superadmin']);
Route::post('/get-branches-by-licence', [BranchController::class, 'getBranchesByLicence']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/licences', [LicenceController::class, 'index']);
    Route::post('/licences', [LicenceController::class, 'store']);
    Route::put('/licences/update/{id}', [LicenceController::class, 'update']);
    Route::delete('/licences/delete/{id}', [LicenceController::class, 'destroy']);
    Route::resource('branch', BranchController::class);
    Route::resource('staff', StaffmasterController::class);
    Route::resource('misc', MiscController::class);
    Route::get('misc', [MiscController::class, 'miscdata']);
    Route::resource('companydetail', CompanydetailCntroller::class);
    Route::resource('admissionform', AdmissionformController::class);
    Route::get('active/status', [AdmissionformController::class, 'activstatus']);
    Route::resource('ledger', LedgermasterController::class);
    Route::resource('profile', UplodeprofileController::class);
    Route::put('/user/change-password', [BranchController::class, 'changePassword']);
    Route::resource('user', UserController::class);
    Route::resource('building',BuildingController::class);
    Route::resource('floor',FloorController::class);
    Route::resource('room',RoomController::class);
    Route::get('/student/assign',[RoomassignController::class, 'admissionroomassign']);
    Route::resource('visitor',VisitorController::class);
    Route::resource('leave',LeaveapplicationController::class);
    Route::resource('roomassign',RoomassignController::class);
    Route::get('/check-room-assignment/{hosteler_id}', [RoomassignController::class, 'checkRoomAssignment']);
    Route::resource('prospect',ProspectController::class);
    Route::post('report',[ProspectController::class,'report']);
    Route::post('createdAtReport',[ProspectController::class,'createdAtReport']);
    Route::post('filterProspects',[ProspectController::class,'filterProspects']);
    Route::resource('feesentry',FeesentryController::class);
    Route::get('getCombinedData',[FeesentryController::class,'getCombinedData']);
    Route::get('/hosteler/data/{licence_no}', [FeesentryController::class, 'getCombinedData']);
    Route::resource('/voucher',VoucherentryController::class);
    Route::resource('/item',ItemmasterController::class);
    Route::post('prospect/{id}/follow-up', [ProspectController::class, 'followUp']);
    Route::get('/prospect/history/{id}', [ProspectController::class, 'getProspectHistory']);




    Route::post('/logout',[AuthController::class,'logout']);
});
Route::get('/test', [TryController::class, 'showContent']);

// Authenticated User Info Route
Route::get('/user', function (Request $request) {
    return $request->user();
});
