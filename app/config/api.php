<?php

declare(strict_types=1);

use Slim\App;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use nrv\adapters\controllers\actions\FiltrerAction;
use nrv\adapters\controllers\actions\GetSoireeAction;
use nrv\adapters\controllers\actions\GetSpectaclesAction;
use nrv\adapters\controllers\actions\CreerCommandeAction;
use nrv\adapters\controllers\actions\CreerSpectacleAction;


use nrv\adapters\controllers\actions\RegisterAction;
use nrv\adapters\controllers\actions\ConnexionAction;
use nrv\adapters\controllers\actions\GetBilletAction;
use nrv\adapters\controllers\actions\GetBilletsCommandeAction;
use nrv\adapters\controllers\actions\GetBilletsUtilisateurAction;
use nrv\adapters\controllers\actions\GetCommandeAction;
use nrv\adapters\controllers\actions\PayerCommandeAction;

return static function (App $app): void {
        
    $app->get('/spectacles[/]', GetSpectaclesAction::class)->setName('GetSpectacles');
    $app->post('/spectacles[/]', CreerSpectacleAction::class)->setName('CreerSpectacle');
    $app->get('/spectacles/filtre', FiltrerAction::class)->setName('FiltrerSpectacles');
    $app->get('/soirees/{id}[/]', GetSoireeAction::class)->setName('GetSoiree');
    $app->post('/commandes', CreerCommandeAction::class);
    $app->get('/commandes/{id}[/]', GetCommandeAction::class)->setName('GetCommande');
    $app->post('/commandes/{id}/paiement[/]', PayerCommandeAction::class)->setName('PayerCommande');
    $app->get('/commandes/{id}/billets[/]', GetBilletsCommandeAction::class)->setName('GetBilletsCommande');
    $app->get('/billets[/]', GetBilletsUtilisateurAction::class)->setName('GetBilletsUtilisateur');
    $app->get('/billets/{token}[/]', GetBilletAction::class)->setName('GetBillet');

    // Ajout pour des tests perso
    $app->get('/connexion[/]', function (Request $request, Response $response): Response {
        $filePath = realpath(__DIR__ . '/../src/adapters/views/auth.html');

        if ($filePath === false || !file_exists($filePath)) {
            $response->getBody()->write(
                "Erreur : Le fichier HTML n'a pas été trouvé. " .
                "Chemin cherché : " . htmlspecialchars(__DIR__ . '/../src/adapters/views/auth.html')
            );
            return $response->withStatus(500);
        }

        $response->getBody()->write(file_get_contents($filePath));
        return $response->withHeader('Content-Type', 'text/html; charset=UTF-8');
    })->setName('ConnexionView');

    // Ajout pour des tests perso
    $app->post('/connexion[/]', ConnexionAction::class)->setName('ConnexionProcess');
    $app->post('/register[/]', RegisterAction::class)->setName('RegisterProcess');
};
