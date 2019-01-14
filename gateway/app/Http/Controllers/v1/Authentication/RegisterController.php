<?php

namespace App\Http\Controllers\v1\Authentication;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use Illuminate\Http\Request;

class RegisterController extends Controller
{

    private $auth_service_client;

    private $mail_service_client;

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

        $this->mail_service_client = new Client([
            // Base URI is used with relative requests
            'base_uri' => env('MAIL_SERVICE_IP'),
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

    public function Register(Request $request)
    {
        try {
            $auth_response = $this->auth_service_client->post('register',
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
                        [
                            'name' => 'activation_link',
                            'contents' => $request->activation_link,
                        ],
                    ],
                    'http_errors' => false,
                ]);

            $auth_response_body = json_decode($auth_response->getBody()->getContents(), true);

            //proceed if the user was registered in the AuthService
            if ($auth_response->getStatusCode() === 200) {

                $mail_response = $this->mail_service_client->post('user/register',
                    [
                        'multipart' => [
                            [
                                'name' => 'email',
                                'contents' => $auth_response_body['data']['user_email'],
                            ],
                            [
                                'name' => 'activation_link',
                                'contents' => $auth_response_body['data']['activation_link'],
                            ],
                        ],
                        'http_errors' => false,
                    ]);

                $mail_response_body = json_decode($mail_response->getBody()->getContents(), true);

                if ($mail_response->getStatusCode() === 200) {

                    $resource_response = $this->resource_service_client->post('/user',
                        [
                            'json' => [
                                'auth_id' => $auth_response_body['data']['auth_id'],
                                'is_invited' => false,
                            ],
                            'http_errors' => false,
                        ]);

                    if ($resource_response->getStatusCode() === 204) {
                        return response()->json([], 204);
                    } else {

                        return response()
                            ->json(
                                [
                                    'data' => json_decode($resource_response->getBody()->getContents(), true),
                                    'statusCode' => $resource_response->getStatusCode(),
                                ]
                            );
                    }
                } else {
                    return response()
                        ->json(
                            [
                                'data' => $mail_response_body,
                                'statusCode' => $mail_response->getStatusCode(),
                            ]
                        );
                }
            } else {
                return response()
                    ->json(
                        [
                            'data' => $auth_response_body,
                            'statusCode' => $auth_response->getStatusCode(),
                        ]
                    );
            }
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                return response()
                    ->json(
                        [
                            'response' => json_decode(Psr7\str($e->getResponse()), true),
                        ]
                    );
            }
            return response()
                ->json(
                    [
                        'request' => json_decode(Psr7\str($e->getRequest()), true),
                    ]
                );
        }

    }

    public function Activate(Request $request)
    {
        $auth_activation_response =
        $this->auth_service_client
            ->post('activate?activation_code=' . $request->activation_code,
                [
                    'http_errors' => false,
                ]);

        if ($auth_activation_response->getStatusCode() === 204) {
            return response()->json([], 204);
        }    

        $auth_activation_response_body = json_decode($auth_activation_response->getBody()->getContents(), true);

        return response()
            ->json(
                [
                    'response' => $auth_activation_response_body,
                ]
            );
    }
}
