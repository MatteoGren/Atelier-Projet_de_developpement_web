<?php

declare(strict_types=1);

// Une erreur fatale PHP n'atteint pas Slim : affichée telle quelle, elle part en 200 sans en-têtes CORS
// et le navigateur montre une fausse erreur CORS. On la renvoie plutôt en JSON 500 avec les en-têtes.
ini_set('display_errors', '0');
register_shutdown_function(static function (): void {
    $erreur = error_get_last();
    $fatales = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
    if ($erreur === null || !in_array($erreur['type'], $fatales, true) || headers_sent()) {
        return;
    }
    http_response_code(500);
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
    header('Access-Control-Allow-Credentials: true');
    echo json_encode(
        ['message' => $erreur['message'] . ' (' . basename($erreur['file']) . ':' . $erreur['line'] . ')'],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
});

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../config/bootstrap.php';
$app->run();
