<?php

use Slim\Factory\AppFactory;
use App\Middleware\CorsMiddleware;
use DI\ContainerBuilder;

require __DIR__ . '/../vendor/autoload.php';

// Create Container
$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
    // Database connection
    \PDO::class => function() {
        $config = require __DIR__ . '/../config/config.php';
        $db = $config['db'];
        
        return new \PDO(
            "mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4",
            $db['user'],
            $db['pass'],
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    },
    
    // Repository definitions with dependencies
    \App\Repositories\CustomerRepository::class => \DI\create()
        ->constructor(\DI\get(\PDO::class)),
    \App\Repositories\MotorRepository::class => \DI\create()
        ->constructor(\DI\get(\PDO::class)),
    \App\Repositories\CommonFaultRepository::class => \DI\create()
        ->constructor(\DI\get(\PDO::class)),
    \App\Repositories\RepairFaultLinksRepository::class => \DI\create()
        ->constructor(\DI\get(\PDO::class)),
    \App\Repositories\MotorCrossSectionLinksRepository::class => \DI\create()
        ->constructor(\DI\get(\PDO::class)),
    \App\Repositories\ImageRepository::class => \DI\create()
        ->constructor(\DI\get(\PDO::class)),
    
    // RepairRepository with dependencies
    \App\Repositories\RepairRepository::class => \DI\create()
        ->constructor(
            \DI\get(\PDO::class),
            \DI\get(\App\Repositories\MotorRepository::class),
            \DI\get(\App\Repositories\CustomerRepository::class),
            \DI\get(\App\Repositories\RepairFaultLinksRepository::class),
            \DI\get(\App\Repositories\ImageRepository::class)
        ),
    
    // Services
    \App\Services\LoggerService::class => \DI\create(),
    \Psr\Log\LoggerInterface::class => \DI\get(\App\Services\LoggerService::class)
]);

$container = $containerBuilder->build();

// Create App
$app = AppFactory::createFromContainer($container);

$app->add(new CorsMiddleware());

// Add error handling middleware
$app->addErrorMiddleware(true, true, true);

// Include routes
require __DIR__ . '/../config/routes.php';

$app->run();
