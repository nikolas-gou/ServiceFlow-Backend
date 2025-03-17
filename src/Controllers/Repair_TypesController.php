<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Repositories\Repair_TypesRepository;

class Repair_TypesController {
    private $repair_typesRepository;

    public function __construct() {
        $this->repair_typesRepository = new Repair_typesRepository();
    }

    public function getAll(Request $request, Response $response) {
        $repair_types = $this->repair_typesRepository->getAll();
        $response->getBody()->write(json_encode($repair_types, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }

}