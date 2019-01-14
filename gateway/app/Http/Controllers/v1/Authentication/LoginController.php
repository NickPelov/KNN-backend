<?php

namespace App\Http\Controllers\v1\Authentication;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    private $auth_service_client;

    private $resource_service_client;
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
        $this->resource_service_client = new Client([
            // Base URI is used with relative requests
            'base_uri' => env('RESOURCE_SERVICE_IP'),
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

        $auth_response_body = json_decode($auth_response->getBody()->getContents(), true);

        if ($auth_response->getStatusCode() === 200) {

            $resource_response = $this->resource_service_client->get('/user',
                [
                    'json' => [
                        'auth_id' => $auth_response_body['data']['auth_id'],
                    ],
                    'http_errors' => false,
                ]);

            $resource_response_body = json_decode($resource_response->getBody()->getContents(), true);

            if ($resource_response->getStatusCode() === 200) {
                
                $auth_response_body['data'] = array_merge($auth_response_body['data'],$resource_response_body['data']);
                unset($auth_response_body['data']['auth_id']);

                return response()->json([
                    "response" => $auth_response_body,
                ]);

            }

            return response()->json([
                "response" => $resource_response_body,
            ]);
        }

        return response()->json([
            "response" => $auth_response_body,
        ]);
    }
}
