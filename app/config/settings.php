<?php

declare(strict_types=1);

return [
    'host' => $_ENV['DB_HOST'] ?? 'nrv.db',
    'port' => $_ENV['DB_PORT'] ?? '5432',
    'database' => $_ENV['POSTGRES_DB'],
    'username' => $_ENV['POSTGRES_USER'],
    'password' => $_ENV['POSTGRES_PASSWORD'],
    'jwt.secret' => $_ENV['JWT_SECRET'],
    // Origines du front autorisées à appeler l'API, séparées par des virgules
    'cors.origines' => array_map('trim', explode(',', $_ENV['CORS_ORIGINS']
        ?? 'http://localhost:31000,http://127.0.0.1:31000,http://docketu.iutnc.univ-lorraine.fr:31000')),
];
