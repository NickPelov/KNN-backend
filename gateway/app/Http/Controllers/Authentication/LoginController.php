<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    private $auth_service_client;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->auth_service_client = new Client([
            // Base URI is used with relative requests
            'base_uri' => env('AUTH_SERVICE_IP'),
            // You can set any number of default request options.
            'timeout' => 2.0,
        ]);
    }
    public function Login(Request $request)
    {
        $auth_response = $this->auth_service_client->post('login',
            [
                'multipart' => [
                    [
                        'name' => 'email',
                        'contents' => $request->email,
                    ],
                    [
                        'name' => 'password',
                        'contents' => $request->password,
                    ],
                ],
                'http_errors' => false,
            ]);

        $auth_responce_body = json_decode($auth_response->getBody()->getContents(), true);

        return response()->json([
            "response" => $auth_responce_body,
            "statusCode" => $auth_response->getStatusCode(),
        ]);
    }
}
