<?php

declare(strict_types=1);

namespace nrv\application\dtos;

use nrv\domain\entities\Spectacle;

// Vue « liste » d'un spectacle : ce que la liste affiche, plus le style pour le filtre.
final class SpectacleListeDTO
{
    public readonly string $id;
    public readonly string $soireeId;
    public readonly string $titre;
    public readonly string $date;
    public readonly string $horaire;
    public readonly ?string $image;
    public readonly string $style;

    public function __construct(
        string $id,
        string $soireeId,
        string $titre,
        string $date,
        string $horaire,
        ?string $image,
        string $style
    ) {
        $this->id = $id;
        $this->soireeId = $soireeId;
        $this->titre = $titre;
        $this->date = $date;
        $this->horaire = $horaire;
        $this->image = $image;
        $this->style = $style;
    }

    public static function fromEntity(Spectacle $spectacle): self
    {
        return new self(
            $spectacle->getId(),
            $spectacle->getSoireeId(),
            $spectacle->getTitre(),
            $spectacle->getHoraire()->format('Y-m-d'),
            $spectacle->getHoraire()->format('H:i'),
            $spectacle->getImagePrincipale(),
            $spectacle->getStyle()
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
            'style' => $this->style,
        ];
    }
}
