<?php

namespace App\Http\Middleware;

use Closure;
use GuzzleHttp\Client;

class VerifyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->headers->has('Authorization')) {
            $token = $request->header('Authorization');

            if (strpos($token, 'Bearer') !== false) {
                $token = explode(" ", $token);
                $token = $token[1];
            }

            $auth_service_client = new Client([
                // Base URI is used with relative requests
                'base_uri' => env('AUTH_SERVICE_IP'),
                // You can set any number of default request options.
                'timeout' => 2.0,
            ]);

            $auth_response = $auth_service_client->post('verify',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                    ],
                    'http_errors' => false,
                ]);

            $verification_response = json_decode($auth_response->getBody()->getContents(), true);

            if (isset($verification_response['data']['user'])) {
                $request->user = $verification_response['data']['user'];

                return $next($request);
            } else {
                return response()->json([
                    "error" => $verification_response,
                ]);
            }

        } else {
            return response()->json([
                "status" => "un-authenticated",
            ]);
        }

    }
}
