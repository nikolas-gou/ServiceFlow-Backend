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

    public function createMotor(Request $request, Response $response): Response
    {
        try {
            $data = json_decode($request->getBody()->getContents(), true);
            
            if (!$data) {
                return ResponseHelper::validationError($response, ['Invalid JSON data']);
            }
            
            $motor = new \App\Models\Motor($data);
            
            if (!$motor->isValid()) {
                return ResponseHelper::validationError($response, ['Motor data is invalid']);
            }
            
            $motorId = $this->motorRepository->createMotor($motor);
            
            if ($motorId) {
                $motor->id = $motorId;
                return ResponseHelper::success($response, $motor, 'Motor created successfully', 201);
            }
            
            return ResponseHelper::serverError($response, 'Failed to create motor');
        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Failed to create motor: ' . $e->getMessage());
        }
    }

    public function getAllBrands(Request $request, Response $response): Response
    {
        try {
            $brands = $this->motorRepository->getAllBrands();
            return ResponseHelper::success($response, $brands, 'Brands retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Failed to retrieve brands: ' . $e->getMessage());
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
