<?php

declare(strict_types=1);

use nrv\application\exceptions\CommandeIntrouvableException;
use nrv\application\exceptions\CommandeInvalideException;
use nrv\application\exceptions\IdentifiantInvalideException;
use nrv\application\exceptions\PaiementInvalideException;
use nrv\application\exceptions\SoireeIntrouvableException;
use nrv\application\dtos\LigneCommandeDTO;
use nrv\application\dtos\PaiementDTO;
use nrv\application\ports\spi\CommandeRepositoryInterface;
use nrv\application\ports\spi\SoireeRepositoryInterface;
use nrv\application\usecases\ServiceCommande;
use nrv\domain\entities\Billet;
use nrv\domain\entities\Commande;
use nrv\domain\entities\Soiree;
use nrv\domain\exceptions\CommandeNonPayableException;
use nrv\domain\exceptions\PlacesInsuffisantesException;

beforeEach(function () {
    // Faux dépôts en mémoire : les tests n'ont besoin ni de base de données ni de Slim.
    $this->soirees = new class implements SoireeRepositoryInterface {
        public function findById(string $id): ?Soiree
        {
            if (!in_array($id, [uneSoiree(1), uneSoiree(2), uneSoiree(3), uneSoiree(4)], true)) {
                return null;
            }
            // La soirée 3 a déjà vendu toutes ses places.
            $placesVendues = $id === uneSoiree(3) ? 100 : 0;
            return new Soiree($id, 'Soirée test', 'Rock', new DateTimeImmutable('2027-06-04 20:00'), 'Nancy', 100, $placesVendues);
        }

        public function findAll(): array
        {
            return [];
        }

        public function save(Soiree $soiree): void
        {
        }
    };

    $this->commandes = new class implements CommandeRepositoryInterface {
        /** @var Commande[] */
        public array $sauvegardees = [];
        /** @var Commande[] commandes déjà en base, indexées par identifiant */
        public array $existantes = [];
        /** @var Commande[] */
        public array $payees = [];

        public function save(Commande $commande): void
        {
            $this->sauvegardees[] = $commande;
        }

        public function findById(string $id): ?Commande
        {
            return $this->existantes[$id] ?? null;
        }

        public function enregistrerPaiement(Commande $commande): void
        {
            $this->payees[] = $commande;
        }
    };

    // Commandes déjà en base : 1 en attente, 2 déjà payée, 3 d'un autre utilisateur, 4 sur une soirée complète.
    $billet = fn (int $commande, int $soiree) => new Billet(
        bin2hex(random_bytes(8)), uneCommande($commande), uneSoiree($soiree), 'NRV-' . $commande . $soiree, bin2hex(random_bytes(32)), false, Soiree::TARIF_NORMAL
    );
    $enAttente = new Commande(uneCommande(1), UTILISATEUR_TEST);
    $enAttente->ajouterBillet($billet(1, 1));
    $enAttente->ajouterBillet($billet(1, 1));
    $enAttente->ajouterBillet($billet(1, 2));
    $payee = new Commande(uneCommande(2), UTILISATEUR_TEST, Commande::STATUT_VALIDEE);
    $payee->ajouterBillet($billet(2, 1));
    $autreUtilisateur = new Commande(uneCommande(3), '11111111-0000-0000-0000-000000000002');
    $autreUtilisateur->ajouterBillet($billet(3, 1));
    $complete = new Commande(uneCommande(4), UTILISATEUR_TEST);
    $complete->ajouterBillet($billet(4, 1));
    $complete->ajouterBillet($billet(4, 3));

    foreach ([$enAttente, $payee, $autreUtilisateur, $complete] as $commande) {
        $this->commandes->existantes[$commande->getIdCommande()] = $commande;
    }

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

it('applique la remise du pass aux trois premières soirées', function () {
    $commande = $this->service->creerCommande(unPanier(
        uneLigne(1, 1, 0),
        uneLigne(2, 1, 0),
        uneLigne(4, 1, 0)
    ));

    expect($commande->montantTotal)->toBe(3 * round(Soiree::TARIF_NORMAL * 0.97, 2));
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
    'soirée complète' => [[uneLigne(3, 1, 0)], PlacesInsuffisantesException::class],
    'plus de billets que de places' => [[uneLigne(1, 60, 41)], PlacesInsuffisantesException::class],
]);

it('refuse un panier invalide et ne sauvegarde rien', function (array $lignes, string $exception) {
    expect(fn () => $this->service->creerCommande(unPanier(...$lignes)))->toThrow($exception);

    expect($this->commandes->sauvegardees)->toBeEmpty();
})->with('paniers refusés');

it('affiche une commande de l\'utilisateur', function () {
    $commande = $this->service->afficherCommande(uneCommande(1), UTILISATEUR_TEST);

    expect($commande->statut)->toBe('en_attente');
    expect($commande->nbBillets)->toBe(3);
    expect($commande->montantTotal)->toBe(3 * Soiree::TARIF_NORMAL);
});

it('ne montre pas la commande d\'un autre utilisateur', function () {
    expect(fn () => $this->service->afficherCommande(uneCommande(3), UTILISATEUR_TEST))
        ->toThrow(CommandeIntrouvableException::class);
});

it('valide la commande quand le paiement est accepté', function () {
    $commande = $this->service->payerCommande(unPaiement(1));

    expect($commande->statut)->toBe('validee');
    expect($this->commandes->payees)->toHaveCount(1);
    expect($this->commandes->payees[0]->getDatePaiement())->not->toBeNull();
    expect($this->commandes->payees[0]->billetsParSoiree())->toBe([uneSoiree(1) => 2, uneSoiree(2) => 1]);
});

dataset('paiements refusés', [
    'carte trop courte' => [unPaiement(1, '424242424242'), PaiementInvalideException::class],
    'carte expirée' => [unPaiement(1, dateExpiration: '01/20'), PaiementInvalideException::class],
    'expiration mal formée' => [unPaiement(1, dateExpiration: '13/30'), PaiementInvalideException::class],
    'code à 2 chiffres' => [unPaiement(1, code: '12'), PaiementInvalideException::class],
    'identifiant invalide' => [new PaiementDTO('pas-un-uuid', UTILISATEUR_TEST, '4242424242424242', '12/99', '123'), IdentifiantInvalideException::class],
    'commande introuvable' => [unPaiement(99), CommandeIntrouvableException::class],
    'commande d\'un autre utilisateur' => [unPaiement(3), CommandeIntrouvableException::class],
    'commande déjà payée' => [unPaiement(2), CommandeNonPayableException::class],
    'soirée complète' => [unPaiement(4), PlacesInsuffisantesException::class],
]);

it('refuse un paiement invalide et n\'enregistre rien', function (PaiementDTO $paiement, string $exception) {
    expect(fn () => $this->service->payerCommande($paiement))->toThrow($exception);

    expect($this->commandes->payees)->toBeEmpty();
})->with('paiements refusés');
