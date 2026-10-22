<?php

declare(strict_types=1);

namespace nrv\domain\entities;

use DateTimeImmutable;

class Spectacle
{
    private string $id;
    private ?string $soireeId;
    private string $titre;
    private string $artistes;
    private ?string $description;
    /** @var string[] chemins des images, la première est l'image principale */
    private array $images;
    private ?string $video;
    private DateTimeImmutable $horaire;
    private string $style;

    /**
     * @param string[] $images
     */
    public function __construct(
        string $id,
        ?string $soireeId,
        string $titre,
        string $artistes,
        ?string $description,
        array $images,
        ?string $video,
        DateTimeImmutable $horaire,
        string $style
    ) {
        $this->id = $id;
        $this->soireeId = $soireeId;
        $this->titre = $titre;
        $this->artistes = $artistes;
        $this->description = $description;
        $this->images = $images;
        $this->video = $video;
        $this->horaire = $horaire;
        $this->style = $style;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSoireeId(): ?string
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
