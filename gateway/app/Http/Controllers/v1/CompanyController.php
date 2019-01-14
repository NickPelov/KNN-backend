<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class CompanyController extends Controller
{

    private $resource_service_client;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->resource_service_client = new Client([
            // Base URI is used with relative requests
            'base_uri' => env('RESOURCE_SERVICE_IP'),
            // You can set any number of default request options.
            'timeout' => 2.0,
        ]);
    }

    public function createCompany(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required|string',
                'description' => 'string',
            ]);
        } catch (Illuminate\Validation\ValidationException $exception) {
            return response()->json(['error' => $exception]);
        }

        $request_body = [
            'auth_id' => $request->user['auth_id'],
            'name' => $request->name,
        ];

        //adding the description only if its not null
        if ($request->description !== null) {
            $request_body['description'] = $request->description;
        }

        $resource_response = $this->resource_service_client->post('/company',
            [
                'json' => $request_body,
                'http_errors' => false,
            ]);

        if ($resource_response->getStatusCode() === 200) {
            $response_object = json_decode($resource_response->getBody()->getContents(), true);
            $response_object['status'] = "ok";
            $response_object['code'] = 200;
            $response_object['messages'] = [];
            $response_object['error'] = [];

            return response()
                ->json(
                    $response_object
                );
        } else {
            return response()
                ->json(
                    [
                        json_decode($resource_response->getBody()->getContents(), true),
                    ]
                );
        }
    }
}
