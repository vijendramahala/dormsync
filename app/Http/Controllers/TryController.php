<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TryController extends Controller
{
     public function showContent()
    {
        // Static ya dynamic content
        $data = [
            'title' => 'Welcome to the API',
            'message' => 'This content is not stored in database.',
            'status' => true
        ];

        return response()->json($data);
    }
}
