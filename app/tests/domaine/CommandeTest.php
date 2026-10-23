<?php

declare(strict_types=1);

use nrv\domain\entities\Billet;
use nrv\domain\entities\Commande;
use nrv\domain\exceptions\CommandeNonPayableException;
use nrv\domain\exceptions\CommandeNonPayeeException;

// Commande de 3 billets : 2 pour la soirée 1 (normal + réduit), 1 pour la soirée 2
function uneCommandeDeTroisBillets(string $statut = Commande::STATUT_EN_ATTENTE): Commande
{
    $commande = new Commande('commande-1', 'utilisateur-1', $statut);
    $commande->ajouterBillet(new Billet('billet-1', 'commande-1', 'soiree-1', 'NRV-1', 'token-1', false, 25.0));
    $commande->ajouterBillet(new Billet('billet-2', 'commande-1', 'soiree-1', 'NRV-2', 'token-2', true, 15.0));
    $commande->ajouterBillet(new Billet('billet-3', 'commande-1', 'soiree-2', 'NRV-3', 'token-3', false, 25.0));
    return $commande;
}

it('compte les billets par soirée', function () {
    expect(uneCommandeDeTroisBillets()->billetsParSoiree())->toBe(['soiree-1' => 2, 'soiree-2' => 1]);
});

it('passe la commande en validée et enregistre la date au paiement', function () {
    $commande = uneCommandeDeTroisBillets();
    $date = new DateTimeImmutable('2026-10-21 12:00');

    $commande->payer($date);

    expect($commande->getStatut())->toBe(Commande::STATUT_VALIDEE)
        ->and($commande->getDatePaiement())->toBe($date);
});

it('refuse un deuxième paiement', function () {
    $commande = uneCommandeDeTroisBillets();
    $commande->payer(new DateTimeImmutable());

    expect(fn () => $commande->payer(new DateTimeImmutable()))->toThrow(CommandeNonPayableException::class);
});

it('refuse de payer une commande annulée', function () {
    expect(fn () => uneCommandeDeTroisBillets(Commande::STATUT_ANNULEE)->verifierPayable())
        ->toThrow(CommandeNonPayableException::class);
});

it('donne accès aux billets une fois payée', function () {
    $commande = uneCommandeDeTroisBillets();
    $commande->payer(new DateTimeImmutable());

    expect($commande->estPayee())->toBeTrue();
    $commande->verifierPayee();
});

it('ne donne pas accès aux billets tant qu\'elle est en attente', function () {
    $commande = uneCommandeDeTroisBillets();

    expect($commande->estPayee())->toBeFalse()
        ->and(fn () => $commande->verifierPayee())->toThrow(CommandeNonPayeeException::class);
});
