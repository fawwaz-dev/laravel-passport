<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Exception;

class InternalRequest
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Kirim internal request ke route API
     * @param string $method  HTTP method (GET, POST, PUT, DELETE)
     * @param string $uri     URI API (misal: 'auth/login')
     * @param array  $data    Data request (form body / query)
     * @return Response
     * @throws Exception jika status code >= 400
     */
    public function request(string $method, string $uri, array $data = [])
    {
        $symfonyRequest = SymfonyRequest::create(
            '/' . ltrim($uri, '/'),
            strtoupper($method),
            $data,
            [],
            [],
            [
                'HTTP_ACCEPT' => 'application/json',
                'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            ]
        );

        // Konversi ke Illuminate Request agar bisa di-handle Laravel
        $request = Request::createFromBase($symfonyRequest);
        $this->app->instance('request', $request);

        $response = $this->app->handle($request);

        if ($response->getStatusCode() >= 400) {
            throw new Exception($response->getContent(), $response->getStatusCode());
        }

        return $response;
    }
}
