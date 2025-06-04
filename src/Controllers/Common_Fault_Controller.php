<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Repositories\Common_Fault_Repository;

class Common_Fault_Controller {
    private $common_fault_repository;

    public function __construct() {
        $this->common_fault_repository = new Common_Fault_Repository();
    }

    public function getAll(Request $request, Response $response) {
        $common_fault = $this->common_fault_repository->getAll();
        $response->getBody()->write(json_encode($common_fault, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }

}