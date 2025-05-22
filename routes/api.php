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
// Public Routes
Route::post('/login', [AuthController::class, 'superAdminLogin']);
Route::post('/superadmin', [BranchController::class, 'superadmin']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/licences', [LicenceController::class, 'index']);
    Route::post('/licences', [LicenceController::class, 'store']);
    Route::put('/licences/{id}', [LicenceController::class, 'update']);
    Route::resource('branch', BranchController::class);
    Route::resource('staff', StaffmasterController::class);
    Route::resource('companydetail', CompanydetailCntroller::class);
    Route::resource('admissionform', AdmissionformController::class);
    Route::resource('ledger', LedgermasterController::class);
    Route::resource('profile', UplodeprofileController::class);
    Route::post('/get-branches-by-licence', [BranchController::class, 'getBranchesByLicence']);
    Route::put('/user/change-password', [BranchController::class, 'changePassword']);
    Route::resource('building',BuildingController::class);
    Route::resource('floor',FloorController::class);
    Route::resource('room',RoomController::class);
    Route::resource('visitor',VisitorController::class);
    Route::resource('leave',LeaveapplicationController::class);
    Route::resource('roomassign',RoomassignController::class);

});

// Authenticated User Info Route
Route::get('/user', function (Request $request) {
    return $request->user();
});
