<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Repositories\CommonFaultRepository;
use App\Helpers\ResponseHelper;

class CommonFaultController
{
    private $commonFaultRepository;

    public function __construct(CommonFaultRepository $commonFaultRepository)
    {
        $this->commonFaultRepository = $commonFaultRepository;
    }

    public function getAll(Request $request, Response $response): Response
    {
        try {
            $commonFaults = $this->commonFaultRepository->getAll();
            return ResponseHelper::success($response, $commonFaults, 'Common faults retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Failed to retrieve common faults: ' . $e->getMessage());
        }
    }
}
