<?php

declare(strict_types=1);

return [
    'host' => $_ENV['DB_HOST'] ?? 'nrv.db',
    'port' => $_ENV['DB_PORT'] ?? '5432',
    'database' => $_ENV['POSTGRES_DB'],
    'username' => $_ENV['POSTGRES_USER'],
    'password' => $_ENV['POSTGRES_PASSWORD']
];
