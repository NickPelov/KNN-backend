<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use Illuminate\Http\Request;

class RegisterController extends Controller
{

    private $auth_service_client;

    private $mail_service_client;

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
                            'name' => 'first_name',
                            'contents' => $request->first_name,
                        ],
                        [
                            'name' => 'last_name',
                            'contents' => $request->last_name,
                        ],
                        [
                            'name' => 'password',
                            'contents' => $request->password,
                        ],
                    ],
                    'http_errors' => false,
                ]);

            $auth_responce_body = json_decode($auth_response->getBody()->getContents(), true);

            if (isset($auth_responce_body['activation_code'])) {
                $mail_responce = $this->mail_service_client->post('user/register',
                    [
                        'multipart' => [
                            [
                                'name' => 'email',
                                'contents' => $request->email,
                            ],
                            [
                                'name' => 'activation_code',
                                'contents' => env('AUTH_SERVICE_IP').'activate?activation_code='.$auth_responce_body['activation_code'],
                            ],
                        ],
                        'http_errors' => false,
                    ]);
                return response()
                    ->json(
                        [
                            'auth_data' => [
                                'body' => $auth_responce_body,
                                'statusCode' => $auth_response->getStatusCode(),
                            ],
                            'mail_data' => [
                                'body' => json_decode($mail_responce->getBody()->getContents(), true),
                                'statusCode' => $mail_responce->getStatusCode(),
                            ],
                        ]
                    );
            } else {
                return response()
                    ->json(
                        [
                            'data' => $auth_responce_body,
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

    }
}
