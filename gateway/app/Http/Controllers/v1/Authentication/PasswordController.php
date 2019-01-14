<?php

namespace App\Http\Controllers\v1\Authentication;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class PasswordController extends Controller
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

    public function ResetPassword(Request $request)
    {
        $auth_response = $this->auth_service_client->post('password/link',
            [
                'multipart' => [
                    [
                        'name' => 'email',
                        'contents' => $request->email,
                    ],
                    [
                        'name' => 'register_link',
                        'contents' => $request->register_link,
                    ],
                ],
                'http_errors' => false,
            ]);

        $auth_responce_body = json_decode($auth_response->getBody()->getContents(), true);

        if (isset($auth_responce_body['reset_code'])) {
            $mail_responce = $this->mail_service_client->post('user/password/reset',
                [
                    'multipart' => [
                        [
                            'name' => 'email',
                            'contents' => $request->email,
                        ],
                        [
                            'name' => 'reset_code',
                            'contents' =>
                            $auth_responce_body['register_link'] . '?reset_code=' . $auth_responce_body['reset_code'],
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

    }
    public function SetNewPassword(Request $request)
    {
        $auth_reset_password_response =
        $this->auth_service_client
            ->post('/password/reset',
                [
                    'multipart' => [
                        [
                            'name' => 'new_password',
                            'contents' => $request->new_password,
                        ],
                        [
                            'name' => 'new_password_confirm',
                            'contents' => $request->new_password_confirm,
                        ],
                        [
                            'name' => 'redirect_link',
                            'contents' => $request->redirect_link,
                        ],
                    ],
                    'http_errors' => false,
                ]);
        $auth_reset_password_response_body = json_decode($auth_reset_password_response->getBody()->getContents(), true);

        return response()
            ->json(
                $auth_reset_password_response_body
            );
    }
}
