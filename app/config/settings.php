<?php
    return [
        'host' => $_ENV['DB_HOST'],
        'port' => $_ENV['DB_PORT'],
        'database' => $_ENV['POSTGRES_DB'],
        'username' => $_ENV['POSTGRES_USER'],
        'password' => $_ENV['POSTGRES_PASSWORD']
    ];
?>