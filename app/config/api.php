<?php

declare(strict_types=1);

use Slim\App;

use nrv\adapters\controllers\actions\AccueilAction;
use nrv\adapters\controllers\actions\GetSoireeAction;
use nrv\adapters\controllers\actions\GetSpectaclesAction;
use nrv\adapters\controllers\actions\CreerCommandeAction;
use nrv\adapters\controllers\actions\CreerSpectacleAction;
use nrv\adapters\controllers\actions\CreerSoireeAction;
use nrv\adapters\controllers\actions\RegisterAction;
use nrv\adapters\controllers\actions\ConnexionAction;
use nrv\adapters\controllers\actions\GetBilletAction;
use nrv\adapters\controllers\actions\GetBilletsCommandeAction;
use nrv\adapters\controllers\actions\GetBilletsUtilisateurAction;
use nrv\adapters\controllers\actions\GetCommandeAction;
use nrv\adapters\controllers\actions\ListerSoireeBackOfficeAction;
use nrv\adapters\controllers\actions\PayerCommandeAction;

return static function (App $app): void {

    $app->get('/', AccueilAction::class)->setName('Accueil');

    // Filtres en paramètres : /spectacles?date=AAAA-MM-JJ&style=...
    $app->get('/spectacles[/]', GetSpectaclesAction::class)->setName('GetSpectacles');
    $app->post('/spectacles[/]', CreerSpectacleAction::class)->setName('CreerSpectacle');
    $app->get('/soirees[/]', ListerSoireeBackOfficeAction::class)->setName('SoireesJauges');
    $app->post('/soirees[/]', CreerSoireeAction::class)->setName('CreerSoiree');
    $app->get('/soirees/{id}[/]', GetSoireeAction::class)->setName('GetSoiree');
    $app->post('/commandes[/]', CreerCommandeAction::class)->setName('CreerCommande');
    $app->get('/commandes/{id}[/]', GetCommandeAction::class)->setName('GetCommande');
    $app->post('/commandes/{id}/paiement[/]', PayerCommandeAction::class)->setName('PayerCommande');
    $app->get('/commandes/{id}/billets[/]', GetBilletsCommandeAction::class)->setName('GetBilletsCommande');
    $app->get('/billets[/]', GetBilletsUtilisateurAction::class)->setName('GetBilletsUtilisateur');
    $app->get('/billets/{token}[/]', GetBilletAction::class)->setName('GetBillet');
    $app->post('/utilisateurs[/]', RegisterAction::class)->setName('CreerUtilisateur');
    $app->post('/sessions[/]', ConnexionAction::class)->setName('CreerSession');
};
