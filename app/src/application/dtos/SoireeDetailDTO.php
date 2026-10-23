<?php

declare(strict_types=1);

namespace nrv\application\dtos;

use nrv\domain\entities\Soiree;
use nrv\domain\entities\Spectacle;

// Vue « détail » d'une soirée : ses informations, ses tarifs et ses spectacles.
final class SoireeDetailDTO
{
    public readonly string $id;
    public readonly string $nom;
    public readonly string $thematique;
    public readonly string $date;
    public readonly string $horaire;
    public readonly string $lieu;
    public readonly array $tarifs;
    public readonly array $spectacles;

    public function __construct(
        string $id,
        string $nom,
        string $thematique,
        string $date,
        string $horaire,
        string $lieu,
        array $tarifs,
        array $spectacles
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->thematique = $thematique;
        $this->date = $date;
        $this->horaire = $horaire;
        $this->lieu = $lieu;
        $this->tarifs = $tarifs;
        $this->spectacles = $spectacles;
    }

    public static function fromEntities(Soiree $soiree, array $spectacles): self
    {
        return new self(
            $soiree->getId(),
            $soiree->getNom(),
            $soiree->getThematique(),
            $soiree->getDate()->format('Y-m-d'),
            $soiree->getDate()->format('H:i'),
            $soiree->getLieu(),
            $soiree->getTarifs(),
            array_map(fn (Spectacle $spectacle) => SpectacleDetailDTO::fromEntity($spectacle), $spectacles)
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'thematique' => $this->thematique,
            'date' => $this->date,
            'horaire' => $this->horaire,
            'lieu' => $this->lieu,
            'tarifs' => $this->tarifs,
            'spectacles' => array_map(fn (SpectacleDetailDTO $dto) => $dto->toArray(), $this->spectacles),
        ];
    }
}
