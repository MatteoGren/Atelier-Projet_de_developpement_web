<?php

declare(strict_types=1);

namespace nrv\application\dtos;

use nrv\domain\entities\Spectacle;

// Vue « détail » d'un spectacle, affichée dans le détail d'une soirée.
final class SpectacleDetailDTO
{
    public readonly string $id;
    public readonly string $titre;
    public readonly string $artistes;
    public readonly ?string $description;
    public readonly string $style;
    public readonly ?string $video;

    public function __construct(
        string $id,
        string $titre,
        string $artistes,
        ?string $description,
        string $style,
        ?string $video
    ) {
        $this->id = $id;
        $this->titre = $titre;
        $this->artistes = $artistes;
        $this->description = $description;
        $this->style = $style;
        $this->video = $video;
    }

    public static function fromEntity(Spectacle $spectacle): self
    {
        return new self(
            $spectacle->getId(),
            $spectacle->getTitre(),
            $spectacle->getArtistes(),
            $spectacle->getDescription(),
            $spectacle->getStyle(),
            $spectacle->getVideo()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'titre' => $this->titre,
            'artistes' => $this->artistes,
            'description' => $this->description,
            'style' => $this->style,
            'video' => $this->video,
        ];
    }
}
