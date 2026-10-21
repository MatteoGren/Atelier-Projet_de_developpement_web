<?php

declare(strict_types=1);

namespace nrv\adapters\persistence;

use DateTime;
use DateTimeImmutable;
use nrv\application\ports\spi\SpectacleRepositoryInterface;
use nrv\domain\entities\Spectacle;
use PDO;

class PDOSpectacleRepository implements SpectacleRepositoryInterface
{
    // Style identique au paramètre, sans tenir compte des majuscules ni des accents ("Métal" = "metal").
    private const STYLE_EGAL = "translate(lower(style), 'àâäéèêëîïôöùûüç', 'aaaeeeeiioouuuc')
        = translate(lower(:style), 'àâäéèêëîïôöùûüç', 'aaaeeeeiioouuuc')";

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

    public function findBySoireeId(string $soireeId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, soiree_id, titre, artistes, description, chemin_images, chemin_video, horaire, style
             FROM spectacle
             WHERE soiree_id = :soiree_id
             ORDER BY horaire ASC'
        );
        $stmt->execute(['soiree_id' => $soireeId]);

        return array_map(fn (array $ligne) => $this->versEntite($ligne), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }



    public function filtrerParDate(DateTime $date): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, soiree_id, titre, artistes, description, chemin_images, chemin_video, horaire, style
             FROM spectacle
             WHERE soiree_id = (SELECT id FROM soiree WHERE CAST(date AS VARCHAR) LIKE :dateSoiree)
             ORDER BY horaire ASC'
        );
        $stmt->execute(['dateSoiree' => $date->format('Y-m-d') . '%']);

        return array_map(fn (array $ligne) => $this->versEntite($ligne), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function filtrerParStyle(string $style): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, soiree_id, titre, artistes, description, chemin_images, chemin_video, horaire, style
             FROM spectacle
             WHERE ' . self::STYLE_EGAL . '
             ORDER BY horaire ASC'
        );
        $stmt->execute(['style' => $style]);

        return array_map(fn (array $ligne) => $this->versEntite($ligne), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function filtrer(DateTime $date, string $style): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, soiree_id, titre, artistes, description, chemin_images, chemin_video, horaire, style
             FROM spectacle
             WHERE ' . self::STYLE_EGAL . '
             AND soiree_id = (SELECT id FROM soiree WHERE CAST(date AS VARCHAR) LIKE :dateSoiree)
             ORDER BY horaire ASC'
        );
        $stmt->execute([
            'dateSoiree' => $date->format('Y-m-d') . '%',
            'style' => $style,
        ]);

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
