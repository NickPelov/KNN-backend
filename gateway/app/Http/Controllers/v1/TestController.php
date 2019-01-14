<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;

class TestController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function test()
    {
        return response()->json([
            "pass" => true,
        ]);
    }
}
