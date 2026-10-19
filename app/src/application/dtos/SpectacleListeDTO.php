<?php

declare(strict_types=1);

namespace nrv\application\dtos;

use nrv\domain\entities\Spectacle;

// Vue « liste » d'un spectacle : uniquement ce que la liste affiche.
final class SpectacleListeDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $soireeId,
        public readonly string $titre,
        public readonly string $date,
        public readonly string $horaire,
        public readonly ?string $image
    ) {
    }

    public static function fromEntity(Spectacle $spectacle): self
    {
        return new self(
            $spectacle->getId(),
            $spectacle->getSoireeId(),
            $spectacle->getTitre(),
            $spectacle->getHoraire()->format('Y-m-d'),
            $spectacle->getHoraire()->format('H:i'),
            $spectacle->getImagePrincipale()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'titre' => $this->titre,
            'date' => $this->date,
            'horaire' => $this->horaire,
            'image' => $this->image,
        ];
    }
}
