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


    public function save(Spectacle $spectacle): string
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO spectacle (id, soiree_id, style, titre, artistes, description, chemin_images, chemin_video, horaire)
             VALUES (:id, :soiree_id, :style, :titre, :artistes, :description, :chemin_images, :chemin_video, :horaire)'
        );

        // Transformation du tableau d'images en chaîne séparée par des points-virgules
        $imagesStr = implode(';', $spectacle->getImages());

        $stmt->execute([
            'id' => $spectacle->getId(),
            'soiree_id' => $spectacle->getSoireeId(),
            'style' => $spectacle->getStyle(),
            'titre' => $spectacle->getTitre(),
            'artistes' => $spectacle->getArtistes(),
            'description' => $spectacle->getDescription(),
            'chemin_images' => $imagesStr !== '' ? $imagesStr : null,
            'chemin_video' => $spectacle->getVideo(),
            'horaire' => $spectacle->getHoraire()->format('Y-m-d H:i:s'),
        ]);

        return $spectacle->getId();
    }


    public function associerSoireeAuxSpectacles(string $soireeId, array $spectacleIds): void
    {
        if (empty($spectacleIds)) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($spectacleIds), '?'));

        $sql = "UPDATE spectacle
                SET soiree_id = ?
                WHERE soiree_id IS NULL AND id IN ($placeholders)";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute(array_merge([$soireeId], $spectacleIds));

        if ($stmt->rowCount() !== count($spectacleIds)) {
            throw new \InvalidArgumentException(
                'Un ou plusieurs spectacles sont introuvables ou déjà associés à une soirée.'
            );
        }
    }



}
