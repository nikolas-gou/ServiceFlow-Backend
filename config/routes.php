<?php

use Slim\Routing\RouteCollectorProxy;
use App\Controllers\CustomerController;
use App\Controllers\MotorController;
use App\Controllers\RepairController;
use App\Controllers\StatsOfCustomerController;
use App\Controllers\StatsOfRepairController;
use App\Controllers\Common_Fault_Controller;
use App\Controllers\StatisticsController;

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
    $group->get('/motors/brands', [MotorController::class, 'getAllBrands']);
    $group->get('/motors/{id}', [MotorController::class, 'getMotorById']);

    // Repairs
    $group->get('/repairs', [RepairController::class, 'getAll']);
    $group->post('/repairs', [RepairController::class, 'createRepair']);
    $group->get('/repairs/{id}', [RepairController::class, 'getRepairById']);

    // Common Faults
    $group->get('/common_faults', [Common_Fault_Controller::class, 'getAll']);

    // Stats
    $group->get('/statsOfCustomers', [StatsOfCustomerController::class, 'stats']);
    $group->get('/statsOfRepair', [StatsOfRepairController::class, 'stats']);

    // Νέα Comprehensive Statistics API
    $group->group('/statistics', function (RouteCollectorProxy $statsGroup) {
        // Γενικά στατιστικά για dashboard cards
        $statsGroup->get('/overview', [StatisticsController::class, 'getOverviewStats']);
        
        // Μηνιαία στατιστικά για charts
        $statsGroup->get('/monthly', [StatisticsController::class, 'getMonthlyStats']);
        
        // Στατιστικά πελατών ανά τύπο
        $statsGroup->get('/customer-types', [StatisticsController::class, 'getCustomerTypeStats']);
        
        // Top μάρκες μοτέρ
        $statsGroup->get('/top-brands', [StatisticsController::class, 'getTopMotorBrands']);
        
        // Ανάλυση εσόδων
        $statsGroup->get('/revenue', [StatisticsController::class, 'getRevenueBreakdown']);
        
        // Στατιστικά επισκευών ανά κατάσταση
        $statsGroup->get('/repair-status', [StatisticsController::class, 'getRepairStatusStats']);
        
        // Ολοκληρωμένα δεδομένα dashboard (όλα μαζί)
        $statsGroup->get('/dashboard', [StatisticsController::class, 'getDashboardData']);
    });
});
