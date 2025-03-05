<?php
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\UserController;

$app->options('/{routes:.+}', function ($request, $response) {
    return $response;
});

$app->group('/api', function (RouteCollectorProxy $group) {
    $group->get('/users', [UserController::class, 'listUsers']);
    $group->post('/users', [UserController::class, 'createUser']);
    $group->get('/users/{id}', [UserController::class, 'getUserById']);
});