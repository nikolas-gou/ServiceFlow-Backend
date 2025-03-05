<?php
use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Middleware\CorsMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->add(new CorsMiddleware());

// Add error handling middleware
$app->addErrorMiddleware(true, true, true);

// Include routes
require __DIR__ . '/../config/routes.php';

$app->run();