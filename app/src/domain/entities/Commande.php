<?php

declare(strict_types=1);

namespace nrv\domain\entities;

use DateTimeImmutable;
use nrv\domain\exceptions\CommandeNonPayableException;
use nrv\domain\exceptions\CommandeNonPayeeException;

class Commande {

    public const STATUT_EN_ATTENTE = 'en_attente';
    public const STATUT_VALIDEE = 'validee';
    public const STATUT_ANNULEE = 'annulee';

    private string $idCommande;
    private string $idUtilisateur;
    private string $statut;
    private float $montantTotal;
    private DateTimeImmutable $dateCommande;
    private ?DateTimeImmutable $datePaiement;

    private array $billets = [];

    public function __construct(
        string $idCommande,
        string $idUtilisateur,
        string $statut = self::STATUT_EN_ATTENTE,
        float $montantTotal = 0.0,
        ?DateTimeImmutable $dateCommande = null,
        ?DateTimeImmutable $datePaiement = null
    ) {
        $this->idCommande = $idCommande;
        $this->idUtilisateur = $idUtilisateur;
        $this->statut = $statut;
        $this->montantTotal = $montantTotal;
        $this->dateCommande = $dateCommande ?? new DateTimeImmutable();
        $this->datePaiement = $datePaiement;
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

    public function billetsParSoiree(): array
    {
        $nbBillets = [];
        foreach ($this->billets as $billet) {
            $nbBillets[$billet->getIdSoiree()] = ($nbBillets[$billet->getIdSoiree()] ?? 0) + 1;
        }
        return $nbBillets;
    }

    public function verifierPayable(): void
    {
        if ($this->statut !== self::STATUT_EN_ATTENTE) {
            throw new CommandeNonPayableException($this->idCommande);
        }
    }

    public function payer(DateTimeImmutable $datePaiement): void
    {
        $this->verifierPayable();

        $this->statut = self::STATUT_VALIDEE;
        $this->datePaiement = $datePaiement;
    }

    public function estPayee(): bool
    {
        return $this->statut === self::STATUT_VALIDEE;
    }

    public function verifierPayee(): void
    {
        if (!$this->estPayee()) {
            throw new CommandeNonPayeeException($this->idCommande);
        }
    }
}