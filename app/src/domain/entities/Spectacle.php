<?php

declare(strict_types=1);

namespace nrv\domain\entities;

use DateTimeImmutable;

class Spectacle
{
    /**
     * @param string[] $images chemins des images, la première est l'image principale
     */
    public function __construct(
        private string $id,
        private string $soireeId,
        private string $titre,
        private string $artistes,
        private ?string $description,
        private array $images,
        private ?string $video,
        private DateTimeImmutable $horaire,
        private string $style
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSoireeId(): string
    {
        return $this->soireeId;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function getArtistes(): string
    {
        return $this->artistes;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return string[]
     */
    public function getImages(): array
    {
        return $this->images;
    }

    public function getImagePrincipale(): ?string
    {
        return $this->images[0] ?? null;
    }

    public function getVideo(): ?string
    {
        return $this->video;
    }

    public function getHoraire(): DateTimeImmutable
    {
        return $this->horaire;
    }

    public function getStyle(): string
    {
        return $this->style;
    }
}
