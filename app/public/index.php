<?php

declare(strict_types=1);

// Une erreur fatale PHP n'atteint pas Slim : affichée telle quelle, elle part en 200 sans en-têtes CORS
// et le navigateur montre une fausse erreur CORS. On la renvoie plutôt en JSON 500 avec les en-têtes,
// sans détail technique côté client (le détail reste dans le journal du serveur).
ini_set('display_errors', '0');
register_shutdown_function(static function () use (&$app): void {
    $erreur = error_get_last();
    $fatales = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
    if ($erreur === null || !in_array($erreur['type'], $fatales, true) || headers_sent()) {
        return;
    }
    error_log($erreur['message'] . ' (' . $erreur['file'] . ':' . $erreur['line'] . ')');
    http_response_code(500);
    header('Content-Type: application/json');
    $origine = $_SERVER['HTTP_ORIGIN'] ?? '';
    try {
        $origines = $app instanceof \Slim\App ? $app->getContainer()->get('cors.origines') : [];
    } catch (\Throwable) {
        $origines = [];
    }
    if (in_array($origine, $origines, true)) {
        header('Access-Control-Allow-Origin: ' . $origine);
        header('Vary: Origin');
    }
    echo json_encode(['message' => 'Erreur interne du serveur.'], JSON_UNESCAPED_UNICODE);
});

$app = null;
require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../config/bootstrap.php';
$app->run();
