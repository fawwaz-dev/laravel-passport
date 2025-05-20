<?php

namespace App\Contracts;

interface MakesInternalRequests
{
    /**
     * Melakukan internal request ke endpoint Laravel.
     *
     * @param string $method HTTP method (GET, POST, dsb)
     * @param string $uri URI relatif (contoh: '/oauth/token')
     * @param array $data Data request (body)
     * @return mixed
     */
    public function request(string $method, string $uri, array $data = []);
}
