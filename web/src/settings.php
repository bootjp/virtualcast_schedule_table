<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => 'php://stdout',
            'level' => \Monolog\Logger::DEBUG,
        ],

        // Medoo setting
        'db' => [
            'database_type' => 'mariadb',
            'database_name' => getenv('MYSQL_DATABASE'),
            'server' => getenv('MYSQL_HOST'),
            'username' => getenv('MYSQL_USER'),
            'password' => getenv('MYSQL_PASSWORD'),
            'charset' => 'utf8mb4',
        ]
    ],
];
