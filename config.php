<?php

declare(strict_types=1);

return [
    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'username' => getenv('DB_USERNAME') ?: 'usuario',
        'password' => getenv('DB_PASSWORD') ?: 'contraseña',
        'database' => getenv('DB_DATABASE') ?: 'pico_placa',
        'port' => getenv('DB_PORT') ? (int) getenv('DB_PORT') : 3306,
        'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
    ],
];
