<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Repositories\CommonFaultRepository;

class CommonFaultController
{
    private $commonFaultRepository;

    public function __construct()
    {
        $this->commonFaultRepository = new CommonFaultRepository();
    }

    public function getAll(Request $request, Response $response)
    {
        $commonFault = $this->commonFaultRepository->getAll();
        $response->getBody()->write(json_encode($commonFault, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
