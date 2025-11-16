<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Repositories\MotorRepository;
use App\Helpers\ResponseHelper;

class MotorController
{
    private $motorRepository;

    public function __construct(MotorRepository $motorRepository)
    {
        $this->motorRepository = $motorRepository;
    }

    public function getAll(Request $request, Response $response): Response
    {
        try {
            $motors = $this->motorRepository->getAll();
            return ResponseHelper::success($response, $motors, 'Motors retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Failed to retrieve motors: ' . $e->getMessage());
        }
    }

    public function getMotorById(Request $request, Response $response, $args): Response
    {
        try {
            $motor = $this->motorRepository->getMotorById($args['id']);
            
            if (!$motor) {
                return ResponseHelper::notFound($response, 'Motor not found');
            }
            
            return ResponseHelper::success($response, $motor, 'Motor retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Failed to retrieve motor: ' . $e->getMessage());
        }
    }
}
