<?php

declare(strict_types=1);

namespace nrv\application\dtos;

use nrv\domain\entities\Billet;
use nrv\domain\entities\Soiree;
use nrv\domain\entities\Utilisateur;

// Sortie = un billet imprimable : acheteur, référence, soirée et catégorie de tarif.
final class BilletDTO
{
    public readonly string $ref;
    public readonly string $token;
    public readonly string $acheteur;
    public readonly string $idSoiree;
    public readonly string $soiree;
    public readonly string $date;
    public readonly string $horaire;
    public readonly string $lieu;
    public readonly string $categorie;
    public readonly float $prix;

    public function __construct(
        string $ref,
        string $token,
        string $acheteur,
        string $idSoiree,
        string $soiree,
        string $date,
        string $horaire,
        string $lieu,
        string $categorie,
        float $prix
    ) {
        $this->ref = $ref;
        $this->token = $token;
        $this->acheteur = $acheteur;
        $this->idSoiree = $idSoiree;
        $this->soiree = $soiree;
        $this->date = $date;
        $this->horaire = $horaire;
        $this->lieu = $lieu;
        $this->categorie = $categorie;
        $this->prix = $prix;
    }

    public static function fromEntities(Billet $billet, Soiree $soiree, Utilisateur $acheteur): self
    {
        return new self(
            $billet->getRef(),
            $billet->getToken(),
            $acheteur->getPrenom() . ' ' . $acheteur->getNom(),
            $soiree->getId(),
            $soiree->getNom(),
            $soiree->getDate()->format('Y-m-d'),
            $soiree->getDate()->format('H:i'),
            $soiree->getLieu(),
            $billet->getCategorie(),
            $billet->getPrix()
        );
    }

    public function toArray(): array
    {
        return [
            'ref' => $this->ref,
            'acheteur' => $this->acheteur,
            'soiree' => $this->soiree,
            'date' => $this->date,
            'horaire' => $this->horaire,
            'lieu' => $this->lieu,
            'categorie' => $this->categorie,
            'prix' => $this->prix,
        ];
    }
}
