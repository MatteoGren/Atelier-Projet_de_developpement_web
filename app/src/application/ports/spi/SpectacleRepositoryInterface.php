<?php

declare(strict_types=1);

namespace nrv\application\ports\spi;

use DateTime;
use nrv\domain\entities\Spectacle;

interface SpectacleRepositoryInterface
{
    /**
     * @return Spectacle[] triés par horaire croissant
     */
    public function findAll(): array;

    /**
     * @return Spectacle[] spectacles de la soirée, triés par horaire croissant
     */
    public function findBySoireeId(string $soireeId): array;

    //fonction pour trier avec la date
    public function filtrerParDate(DateTime $date): array;

    //fonction pour trier par le style de musique
    public function filtrerParStyle(String $style): array;

    //fonction qui englobe les deux filtres
    public function filtrer(DateTime $date, String $style): array;
}
