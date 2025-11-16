<?php

return [
    'app' => [
        'name' => 'ServiceFlow API',
        'version' => '1.0.0',
        'debug' => $_ENV['APP_DEBUG'] ?? true,
        'url' => $_ENV['APP_URL'] ?? 'http://localhost:3000'
    ],
    
    'database' => [
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'name' => $_ENV['DB_NAME'] ?? 'motor_service',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'pass' => $_ENV['DB_PASS'] ?? '',
        'port' => $_ENV['DB_PORT'] ?? 3306,
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    ],
    
    'cors' => [
        'allowed_origins' => explode(
            ',',
            $_ENV['CORS_ALLOWED_ORIGINS'] ?? implode(',', [
                'http://localhost:3000',
                'https://motorserviceflow.vercel.app',
                'http://192.168.2.240:3000'
            ])
        ),
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
        'allowed_headers' => ['X-Requested-With', 'Content-Type', 'Accept', 'Origin', 'Authorization'],
        'allow_credentials' => true
    ],
    
    'logging' => [
        'level' => $_ENV['LOG_LEVEL'] ?? 'info',
        'file' => $_ENV['LOG_FILE'] ?? 'logs/app.log'
    ]
];
