<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;

class FailedInternalRequestException extends Exception
{
    protected $request;
    protected $response;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct('Internal request failed', $response->getStatusCode());
        $this->request = $request;
        $this->response = $response;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getResponse()
    {
        return $this->response;
    }
}
