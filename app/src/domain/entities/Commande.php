<?php

namespace nrv\domain\entities;

use DateTimeImmutable;
use nrv\domain\exceptions\CommandeNonPayableException;

class Commande {

    public const STATUT_EN_ATTENTE = 'en_attente';
    public const STATUT_VALIDEE = 'validee';
    public const STATUT_ANNULEE = 'annulee';

    private float $montantTotal;
    private DateTimeImmutable $dateCommande;

    private array $billets = [];

    public function __construct(
        private string $idCommande,
        private string $idUtilisateur,
        private string $statut = self::STATUT_EN_ATTENTE,
        float $montantTotal = 0.0,
        ?DateTimeImmutable $dateCommande = null,
        private ?DateTimeImmutable $datePaiement = null
    ) {
        $this->montantTotal = $montantTotal;
        $this->dateCommande = $dateCommande ?? new DateTimeImmutable();
    }

    public function getIdCommande(): string { return $this->idCommande; }
    public function getIdUtilisateur(): string { return $this->idUtilisateur; }
    public function getStatut(): string { return $this->statut; }
    public function getMontantTotal(): float { return $this->montantTotal; }
    public function getDateCommande(): DateTimeImmutable { return $this->dateCommande; }
    public function getDatePaiement(): ?DateTimeImmutable { return $this->datePaiement; }

    public function ajouterBillet(Billet $billet): void
    {
        $this->billets[] = $billet;
        $this->montantTotal = round($this->montantTotal + $billet->getPrix(), 2);
    }

    public function getBillets(): array
    {
        return $this->billets;
    }

    /**
     * @return array<string, int> nombre de billets par identifiant de soirée
     */
    public function billetsParSoiree(): array
    {
        $nbBillets = [];
        foreach ($this->billets as $billet) {
            $nbBillets[$billet->getIdSoiree()] = ($nbBillets[$billet->getIdSoiree()] ?? 0) + 1;
        }
        return $nbBillets;
    }

    /**
     * @throws CommandeNonPayableException
     */
    public function verifierPayable(): void
    {
        if ($this->statut !== self::STATUT_EN_ATTENTE) {
            throw new CommandeNonPayableException($this->idCommande);
        }
    }

    /**
     * @throws CommandeNonPayableException
     */
    public function payer(DateTimeImmutable $datePaiement): void
    {
        $this->verifierPayable();

        $this->statut = self::STATUT_VALIDEE;
        $this->datePaiement = $datePaiement;
    }
}