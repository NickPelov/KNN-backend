<?php

namespace App\Http\Controllers\Authentication;

use GuzzleHttp\Client;
use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use Illuminate\Http\Request;

class PasswordController extends Controller
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

    public function ResetPassword(Request $requset)
    {

    }
    public function SetNewPassword(Request $requset)
    {

    }
}
