<?php

declare(strict_types=1);

use nrv\application\exceptions\CommandeInvalideException;
use nrv\application\exceptions\IdentifiantInvalideException;
use nrv\application\exceptions\SoireeIntrouvableException;
use nrv\application\dtos\LigneCommandeDTO;
use nrv\application\ports\spi\CommandeRepositoryInterface;
use nrv\application\ports\spi\SoireeRepositoryInterface;
use nrv\application\usecases\ServiceCommande;
use nrv\domain\entities\Commande;
use nrv\domain\entities\Soiree;

beforeEach(function () {
    // Faux dépôts en mémoire : les tests n'ont besoin ni de base de données ni de Slim.
    $this->soirees = new class implements SoireeRepositoryInterface {
        public function findById(string $id): ?Soiree
        {
            if (!in_array($id, [uneSoiree(1), uneSoiree(2)], true)) {
                return null;
            }
            return new Soiree($id, 'Soirée test', 'Rock', new DateTimeImmutable('2027-06-04 20:00'), 'Nancy', 100);
        }
    };

    $this->commandes = new class implements CommandeRepositoryInterface {
        /** @var Commande[] */
        public array $sauvegardees = [];

        public function save(Commande $commande): void
        {
            $this->sauvegardees[] = $commande;
        }
    };

    $this->service = new ServiceCommande($this->soirees, $this->commandes);
});

it('crée une commande en attente pour l\'utilisateur', function () {
    $commande = $this->service->creerCommande(unPanier(uneLigne(1, 2, 1)));

    expect($commande->statut)->toBe('en_attente');
    expect($commande->nbBillets)->toBe(3);
    expect($this->commandes->sauvegardees)->toHaveCount(1);
    expect($this->commandes->sauvegardees[0]->getIdUtilisateur())->toBe(UTILISATEUR_TEST);
});

it('calcule le montant total avec les tarifs du domaine', function () {
    // soirée 1 : 2 normaux + 1 réduit ; soirée 2 : 3 réduits
    $commande = $this->service->creerCommande(unPanier(uneLigne(1, 2, 1), uneLigne(2, 0, 3)));

    expect($commande->montantTotal)->toBe(2 * Soiree::TARIF_NORMAL + 4 * Soiree::TARIF_REDUIT);
    expect($commande->nbBillets)->toBe(6);
});

it('crée un billet par place avec le bon tarif', function () {
    $this->service->creerCommande(unPanier(uneLigne(1, 2, 1)));

    $sauvegardee = $this->commandes->sauvegardees[0];
    $billets = $sauvegardee->getBillets();
    $reduits = array_values(array_filter($billets, fn ($b) => $b->isTarifReduit()));
    $normaux = array_values(array_filter($billets, fn ($b) => !$b->isTarifReduit()));

    expect($billets)->toHaveCount(3);
    expect($reduits)->toHaveCount(1);
    expect($reduits[0]->getPrix())->toBe(Soiree::TARIF_REDUIT);
    expect($normaux)->toHaveCount(2);
    expect($normaux[0]->getPrix())->toBe(Soiree::TARIF_NORMAL);
});

it('rattache chaque billet à sa commande et à sa soirée', function () {
    $this->service->creerCommande(unPanier(uneLigne(1, 1, 0), uneLigne(2, 2, 0)));

    $sauvegardee = $this->commandes->sauvegardees[0];
    $parSoiree = [uneSoiree(1) => 0, uneSoiree(2) => 0];

    foreach ($sauvegardee->getBillets() as $billet) {
        expect($billet->getIdCommande())->toBe($sauvegardee->getIdCommande());
        $parSoiree[$billet->getIdSoiree()]++;
    }

    expect($parSoiree)->toBe([uneSoiree(1) => 1, uneSoiree(2) => 2]);
});

it('génère une référence et un token uniques par billet', function () {
    $this->service->creerCommande(unPanier(uneLigne(1, 5, 5)));

    $billets = $this->commandes->sauvegardees[0]->getBillets();
    $tokens = [];
    $refs = [];

    foreach ($billets as $billet) {
        expect($billet->getToken())->toMatch('/^[0-9a-f]{64}$/');
        expect($billet->getRef())->toMatch('/^NRV-[0-9A-F]{8}$/');
        $tokens[] = $billet->getToken();
        $refs[] = $billet->getRef();
    }

    expect(array_unique($tokens))->toHaveCount(10);
    expect(array_unique($refs))->toHaveCount(10);
});

dataset('paniers refusés', [
    'panier vide' => [[], CommandeInvalideException::class],
    'quantité négative' => [[uneLigne(1, -1, 2)], CommandeInvalideException::class],
    'ligne sans billet' => [[uneLigne(1, 0, 0)], CommandeInvalideException::class],
    'soirée en double' => [[uneLigne(1, 1, 0), uneLigne(1, 0, 1)], CommandeInvalideException::class],
    'identifiant invalide' => [[new LigneCommandeDTO('pas-un-uuid', 1, 0)], IdentifiantInvalideException::class],
    'soirée introuvable' => [[uneLigne(99, 1, 0)], SoireeIntrouvableException::class],
    'soirée introuvable en 2e ligne' => [[uneLigne(1, 1, 0), uneLigne(99, 1, 0)], SoireeIntrouvableException::class],
]);

it('refuse un panier invalide et ne sauvegarde rien', function (array $lignes, string $exception) {
    expect(fn () => $this->service->creerCommande(unPanier(...$lignes)))->toThrow($exception);

    expect($this->commandes->sauvegardees)->toBeEmpty();
})->with('paniers refusés');
