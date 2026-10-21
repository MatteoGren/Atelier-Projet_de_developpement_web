<?php

declare(strict_types=1);

namespace nrv\application\usecases;

use nrv\application\dtos\BilletDTO;
use nrv\application\exceptions\BilletIntrouvableException;
use nrv\application\exceptions\CommandeIntrouvableException;
use nrv\application\exceptions\SoireeIntrouvableException;
use nrv\application\exceptions\UtilisateurIntrouvableException;
use nrv\application\ports\api\ServiceBilletInterface;
use nrv\application\ports\spi\BilletRepositoryInterface;
use nrv\application\ports\spi\CommandeRepositoryInterface;
use nrv\application\ports\spi\SoireeRepositoryInterface;
use nrv\application\ports\spi\UtilisateurRepositoryInterface;
use nrv\application\validators\IdentifiantValidator;
use nrv\domain\entities\Billet;
use nrv\domain\entities\Commande;
use nrv\domain\entities\Soiree;
use nrv\domain\entities\Utilisateur;

class ServiceBillet implements ServiceBilletInterface
{
    private BilletRepositoryInterface $billetRepository;
    private CommandeRepositoryInterface $commandeRepository;
    private SoireeRepositoryInterface $soireeRepository;
    private UtilisateurRepositoryInterface $utilisateurRepository;

    public function __construct(
        BilletRepositoryInterface $billetRepository,
        CommandeRepositoryInterface $commandeRepository,
        SoireeRepositoryInterface $soireeRepository,
        UtilisateurRepositoryInterface $utilisateurRepository
    ) {
        $this->billetRepository = $billetRepository;
        $this->commandeRepository = $commandeRepository;
        $this->soireeRepository = $soireeRepository;
        $this->utilisateurRepository = $utilisateurRepository;
    }

    public function afficherBilletsCommande(string $idCommande, string $idUtilisateur): array
    {
        IdentifiantValidator::valider($idCommande);

        $commande = $this->commandeRepository->findById($idCommande);
        if ($commande === null || $commande->getIdUtilisateur() !== $idUtilisateur) {
            throw new CommandeIntrouvableException($idCommande);
        }
        $commande->verifierPayee();

        $acheteur = $this->trouverAcheteur($commande);
        $soirees = [];
        $billets = [];

        foreach ($commande->getBillets() as $billet) {
            // Une commande contient souvent plusieurs billets pour la même soirée : on ne la charge qu'une fois.
            $idSoiree = $billet->getIdSoiree();
            $soirees[$idSoiree] = $soirees[$idSoiree] ?? $this->trouverSoiree($idSoiree);
            $billets[] = BilletDTO::fromEntities($billet, $soirees[$idSoiree], $acheteur);
        }

        return $billets;
    }

    public function afficherBillet(string $token): BilletDTO
    {
        // Un token mal formé est traité comme inconnu : la base n'est pas interrogée.
        if (preg_match('/^[0-9a-f]{64}$/', $token) !== 1) {
            throw new BilletIntrouvableException();
        }

        $billet = $this->billetRepository->findByToken($token);
        if ($billet === null) {
            throw new BilletIntrouvableException();
        }

        // Le billet n'existe pour le public qu'une fois sa commande payée.
        $commande = $this->commandeRepository->findById($billet->getIdCommande());
        if ($commande === null || !$commande->estPayee()) {
            throw new BilletIntrouvableException();
        }

        return BilletDTO::fromEntities($billet, $this->trouverSoiree($billet->getIdSoiree()), $this->trouverAcheteur($commande));
    }

    private function trouverAcheteur(Commande $commande): Utilisateur
    {
        $acheteur = $this->utilisateurRepository->findById($commande->getIdUtilisateur());
        if ($acheteur === null) {
            throw new UtilisateurIntrouvableException($commande->getIdUtilisateur());
        }
        return $acheteur;
    }

    private function trouverSoiree(string $idSoiree): Soiree
    {
        $soiree = $this->soireeRepository->findById($idSoiree);
        if ($soiree === null) {
            throw new SoireeIntrouvableException($idSoiree);
        }
        return $soiree;
    }
}
