<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\test;

class TesrController extends Controller
{
    public function index(){

        return view('test');
    }
}
