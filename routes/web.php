<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TesrController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('test',[TesrController::class,'index']);

