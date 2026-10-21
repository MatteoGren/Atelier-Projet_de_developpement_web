<?php

declare(strict_types=1);

use nrv\application\exceptions\BilletIntrouvableException;
use nrv\application\exceptions\CommandeIntrouvableException;
use nrv\application\exceptions\IdentifiantInvalideException;
use nrv\application\exceptions\UtilisateurIntrouvableException;
use nrv\application\ports\spi\BilletRepositoryInterface;
use nrv\application\ports\spi\CommandeRepositoryInterface;
use nrv\application\ports\spi\SoireeRepositoryInterface;
use nrv\application\ports\spi\UtilisateurRepositoryInterface;
use nrv\application\usecases\ServiceBillet;
use nrv\domain\entities\Billet;
use nrv\domain\entities\Commande;
use nrv\domain\entities\Soiree;
use nrv\domain\entities\Utilisateur;
use nrv\domain\exceptions\CommandeNonPayeeException;

// Token du billet n° $numero, au même format que les vrais (64 caractères hexadécimaux)
function unToken(int $numero): string
{
    return sprintf('%064d', $numero);
}

beforeEach(function () {
    // Faux dépôts en mémoire : les tests n'ont besoin ni de base de données ni de Slim.
    $soirees = new class implements SoireeRepositoryInterface {
        public function findById(string $id): ?Soiree
        {
            if ($id !== uneSoiree(1)) {
                return null;
            }
            return new Soiree($id, 'Ouverture Électrique', 'Rock', new DateTimeImmutable('2027-06-04 20:00'), 'Place Stanislas', 100);
        }
    };

    $utilisateurs = new class implements UtilisateurRepositoryInterface {
        public function findByEmail(string $email): ?Utilisateur
        {
            return null;
        }

        public function findById(string $id): ?Utilisateur
        {
            if ($id === 'inconnu') {
                return null;
            }
            return new Utilisateur($id, 'Martin', 'Léa', 'lea@example.com', '', 'hash', 'client');
        }

        public function save(Utilisateur $utilisateur): void
        {
        }
    };

    // Commandes : 1 payée (billets 1 et 2), 2 en attente (billet 3), 3 payée d'un autre utilisateur (billet 4).
    $billet = fn (int $numero, int $commande, bool $reduit) => new Billet(
        'billet-' . $numero, uneCommande($commande), uneSoiree(1), 'NRV-000' . $numero, unToken($numero), $reduit, $reduit ? Soiree::TARIF_REDUIT : Soiree::TARIF_NORMAL
    );
    $payee = new Commande(uneCommande(1), UTILISATEUR_TEST, Commande::STATUT_VALIDEE);
    $payee->ajouterBillet($billet(1, 1, false));
    $payee->ajouterBillet($billet(2, 1, true));
    $enAttente = new Commande(uneCommande(2), UTILISATEUR_TEST);
    $enAttente->ajouterBillet($billet(3, 2, false));
    $autreUtilisateur = new Commande(uneCommande(3), '11111111-0000-0000-0000-000000000002', Commande::STATUT_VALIDEE);
    $autreUtilisateur->ajouterBillet($billet(4, 3, false));

    $commandes = new class implements CommandeRepositoryInterface {
        /** @var Commande[] indexées par identifiant */
        public array $existantes = [];

        public function save(Commande $commande): void
        {
        }

        public function findById(string $id): ?Commande
        {
            return $this->existantes[$id] ?? null;
        }

        public function enregistrerPaiement(Commande $commande): void
        {
        }
    };

    $billets = new class ($commandes) implements BilletRepositoryInterface {
        /** @var Billet[] indexés par token */
        public array $existants = [];
        private CommandeRepositoryInterface $commandes;

        public function __construct(CommandeRepositoryInterface $commandes)
        {
            $this->commandes = $commandes;
        }

        public function findByToken(string $token): ?Billet
        {
            return $this->existants[$token] ?? null;
        }

        public function findPayesByUtilisateur(string $idUtilisateur): array
        {
            return array_values(array_filter($this->existants, function (Billet $billet) use ($idUtilisateur) {
                $commande = $this->commandes->findById($billet->getIdCommande());
                return $commande->getIdUtilisateur() === $idUtilisateur && $commande->estPayee();
            }));
        }
    };

    foreach ([$payee, $enAttente, $autreUtilisateur] as $commande) {
        $commandes->existantes[$commande->getIdCommande()] = $commande;
        foreach ($commande->getBillets() as $b) {
            $billets->existants[$b->getToken()] = $b;
        }
    }

    $this->service = new ServiceBillet($billets, $commandes, $soirees, $utilisateurs);
});

it('renvoie les billets d\'une commande payée avec les informations à imprimer', function () {
    $billets = $this->service->afficherBilletsCommande(uneCommande(1), UTILISATEUR_TEST);

    expect($billets)->toHaveCount(2);
    expect($billets[0]->acheteur)->toBe('Léa Martin');
    expect($billets[0]->ref)->toBe('NRV-0001');
    expect($billets[0]->soiree)->toBe('Ouverture Électrique');
    expect($billets[0]->date)->toBe('2027-06-04');
    expect($billets[0]->horaire)->toBe('20:00');
    expect($billets[0]->categorie)->toBe('normal');
    expect($billets[1]->categorie)->toBe('reduit');
});

dataset('commandes sans billets', [
    'commande en attente' => [uneCommande(2), CommandeNonPayeeException::class],
    'commande d\'un autre utilisateur' => [uneCommande(3), CommandeIntrouvableException::class],
    'commande introuvable' => [uneCommande(99), CommandeIntrouvableException::class],
    'identifiant invalide' => ['pas-un-uuid', IdentifiantInvalideException::class],
]);

it('refuse les billets d\'une commande non accessible', function (string $idCommande, string $exception) {
    expect(fn () => $this->service->afficherBilletsCommande($idCommande, UTILISATEUR_TEST))->toThrow($exception);
})->with('commandes sans billets');

it('affiche un billet payé à partir de son token, sans connexion', function () {
    $billet = $this->service->afficherBillet(unToken(4));

    expect($billet->ref)->toBe('NRV-0004');
    expect($billet->token)->toBe(unToken(4));
});

dataset('tokens refusés', [
    'token inconnu' => [unToken(99)],
    'billet d\'une commande non payée' => [unToken(3)],
    'token mal formé' => ['pas-un-token'],
]);

it('ne révèle pas un billet inaccessible', function (string $token) {
    expect(fn () => $this->service->afficherBillet($token))->toThrow(BilletIntrouvableException::class);
})->with('tokens refusés');

it('renvoie tous les billets payés de l\'utilisateur, toutes commandes confondues', function () {
    $billets = $this->service->afficherBilletsUtilisateur(UTILISATEUR_TEST);

    expect(array_map(fn ($billet) => $billet->ref, $billets))->toBe(['NRV-0001', 'NRV-0002']);
    expect($billets[0]->acheteur)->toBe('Léa Martin');
});

it('renvoie une liste vide pour un utilisateur sans billet payé', function () {
    expect($this->service->afficherBilletsUtilisateur('11111111-0000-0000-0000-000000000009'))->toBe([]);
});

it('refuse les billets d\'un utilisateur inconnu', function () {
    expect(fn () => $this->service->afficherBilletsUtilisateur('inconnu'))->toThrow(UtilisateurIntrouvableException::class);
});
