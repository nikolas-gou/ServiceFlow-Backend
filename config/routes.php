<?php
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\CustomerController;
use App\Controllers\StatsOfCustomerController;

$app->options('/{routes:.+}', function ($request, $response) {
    return $response;
});

$app->group('/api', function (RouteCollectorProxy $group) {
    $group->get('/customers', [CustomerController::class, 'getAll']);
    $group->post('/customers', [CustomerController::class, 'createCustomer']);
    $group->get('/customers/{id}', [CustomerController::class, 'getCustomerById']);

    // Stats
    $group->get('/statsOfCustomers', [StatsOfCustomerController::class, 'stats']);
});