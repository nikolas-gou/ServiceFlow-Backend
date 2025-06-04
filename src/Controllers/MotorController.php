<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Repositories\MotorRepository;

class MotorController
{
    private $motorRepository;

    public function __construct()
    {
        $this->motorRepository = new MotorRepository();
    }

    public function getAll(Request $request, Response $response)
    {
        $motors = $this->motorRepository->getAll();
        $response->getBody()->write(json_encode($motors, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function getMotorById(Request $request, Response $response, $args)
    {
        $motor = $this->motorRepository->getMotorById($args['id']);
        $response->getBody()->write(json_encode($motor));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function getAllBrands(Request $request, Response $response)
    {
        $brands = $this->motorRepository->getAllBrands();
        $response->getBody()->write(json_encode($brands, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
