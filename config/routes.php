<?php
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\CustomerController;
use App\Controllers\MotorController;
use App\Controllers\StatsOfCustomerController;

$app->options('/{routes:.+}', function ($request, $response) {
    return $response;
});

$app->group('/api', function (RouteCollectorProxy $group) {
    $group->get('/customers', [CustomerController::class, 'getAll']);
    $group->post('/customers', [CustomerController::class, 'createCustomer']);
    $group->get('/customers/{id}', [CustomerController::class, 'getCustomerById']);

    // Motors
    $group->get('/motors', [MotorController::class, 'getAll']);
    $group->post('/motors', [MotorController::class, 'createMotor']);
    $group->get('/motors/{id}', [MotorController::class, 'getMotorById']);

    // Stats
    $group->get('/statsOfCustomers', [StatsOfCustomerController::class, 'getStats']);
});