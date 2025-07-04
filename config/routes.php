<?php

use Slim\Routing\RouteCollectorProxy;
use App\Controllers\CustomerController;
use App\Controllers\MotorController;
use App\Controllers\RepairController;
use App\Controllers\CommonFaultController;
use App\Controllers\StatisticsController;

$app->options('/{routes:.+}', function ($request, $response) {
    return $response;
});

$app->group('/api', function (RouteCollectorProxy $group) {
    // Customers
    $group->get('/customers', [CustomerController::class, 'getAll']);
    $group->post('/customers', [CustomerController::class, 'createCustomer']);
    $group->get('/customers/{id}', [CustomerController::class, 'getCustomerById']);

    // Motors
    $group->get('/motors', [MotorController::class, 'getAll']);
    $group->post('/motors', [MotorController::class, 'createMotor']);
    $group->get('/motors/brands', [MotorController::class, 'getAllBrands']);
    $group->get('/motors/{id}', [MotorController::class, 'getMotorById']);

    // Repairs
    $group->get('/repairs', [RepairController::class, 'getAll']);
    $group->post('/repairs', [RepairController::class, 'createRepair']);
    $group->get('/repairs/{id}', [RepairController::class, 'getRepairById']);
    $group->patch('/repairs/{id}/soft-delete', [RepairController::class, 'softDelete']);

    // Common Faults
    $group->get('/common_faults', [CommonFaultController::class, 'getAll']);

    // Statistics
    $group->group('/statistics', function (RouteCollectorProxy $statsGroup) {
        $statsGroup->get('/overview', [StatisticsController::class, 'getOverviewStats']);
        $statsGroup->get('/dashboard', [StatisticsController::class, 'getDashboardData']);
        $statsGroup->get('/customers', [StatisticsController::class, 'getCustomerStats']);
    });
});
