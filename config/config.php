<?php
    // Δοκιμή με διαφορετικό τρόπο αναγνώρισης του περιβάλλοντος
    $server_name = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
    $http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    
    // Έλεγχος και με τις δύο μεταβλητές
    $is_local = ($server_name == 'localhost' || 
                 $http_host == 'localhost' || 
                 $server_name == '127.0.0.1' ||
                 $http_host == '127.0.0.1' ||
                 strpos($server_name, 'localhost') !== false ||
                 strpos($http_host, 'localhost') !== false ||
                 strpos($server_name, '127.0.0.1') !== false ||
                 strpos($http_host, '127.0.0.1') !== false);
    
    // Debug line - μπορείς να το σχολιάσεις αργότερα
    // file_put_contents('debug_env.txt', "Server: {$server_name}, Host: {$http_host}, IsLocal: " . ($is_local ? 'Yes' : 'No') . "\n", FILE_APPEND);
    
    // Ρυθμίσεις ανάλογα με το περιβάλλον
    $config = [
        'db' => [
            'host' => $is_local ? '127.0.0.1' : 'sql112.infinityfree.com',
            'user' => $is_local ? 'root' : 'if0_38454359',
            'pass' => $is_local ? '' : 'YTqJQsmLhkO6sE',
            'name' => $is_local ? 'motor_service' : 'if0_38454359_motor_service',
            'port' => $is_local ? 3306 : 3306
        ],
        'displayErrorDetails' => $is_local,
        'base_url' => $is_local ? 'http://localhost:3000' : 'serviceflow.infinityfreeapp.com'
    ];

    return $config;