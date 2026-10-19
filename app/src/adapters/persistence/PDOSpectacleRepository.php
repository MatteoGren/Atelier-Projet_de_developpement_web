<?php

declare(strict_types=1);

namespace nrv\adapters\persistence;

use DateTimeImmutable;
use nrv\application\ports\spi\SpectacleRepositoryInterface;
use nrv\domain\entities\Spectacle;
use PDO;

class PDOSpectacleRepository implements SpectacleRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, soiree_id, titre, artistes, description, chemin_images, chemin_video, horaire, style
             FROM spectacle
             ORDER BY horaire ASC'
        );
        $stmt->execute();

        return array_map(fn (array $ligne) => $this->versEntite($ligne), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function versEntite(array $ligne): Spectacle
    {
        // chemin_images contient plusieurs chemins séparés par ';'
        $images = $ligne['chemin_images'] === null || $ligne['chemin_images'] === ''
            ? []
            : array_map('trim', explode(';', $ligne['chemin_images']));

        return new Spectacle(
            $ligne['id'],
            $ligne['soiree_id'],
            $ligne['titre'],
            $ligne['artistes'],
            $ligne['description'],
            $images,
            $ligne['chemin_video'],
            new DateTimeImmutable($ligne['horaire']),
            $ligne['style']
        );
    }
}
