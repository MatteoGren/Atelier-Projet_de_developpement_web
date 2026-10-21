<?php

declare(strict_types=1);

namespace nrv\adapters\persistence;

use DateTime;
use DateTimeImmutable;
use nrv\application\dtos\SpectacleListeDTO;
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



    public function filtrerParDate(DateTime $date): array{
        $stmt = $this->pdo->prepare(
            'SELECT id, soiree_id, titre, artistes, description, chemin_images, chemin_video, horaire, style
             FROM spectacle
             WHERE soiree_id = (SELECT id
                                  FROM soiree
                                  WHERE CAST(date as VARCHAR) LIKE :dateSoiree)                
                                ');     //selectionne ls valeurs des specatcle dont soiree à la bonne date

        $stmt->execute(['dateSoiree' => $date->format('Y-m-d') .'%']);

        $spectacles = [];   

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {        //on crée le tableau de spectacle
            $spectacles[] =  $this->versEntite($row);
        }

        return $spectacles; //renvoie un tableau de Spectacle
    }

    public function filtrerParStyle(String $style): array{
        $stmt = $this->pdo->prepare(
            'SELECT id, soiree_id, titre, artistes, description, chemin_images, chemin_video, horaire, style
             FROM spectacle
             WHERE style LIKE :style');     //selectionne ls valeurs des specatcle dont soiree à la bonne date

        $stmt->execute(['style' => '%'.$style.'%']);

        $spectacles = [];   

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {        //on crée le tableau de spectacle
            $spectacles[] =  $this->versEntite($row);
        }

        return $spectacles; //renvoie un tableau de Spectacle
    }


    public function filtrer(DateTime $date, String $style): array{
        $stmt = $this->pdo->prepare(
            'SELECT id, soiree_id, titre, artistes, description, chemin_images, chemin_video, horaire, style
             FROM spectacle
             WHERE style LIKE :style
             AND soiree_id = (SELECT id
                                  FROM soiree
                                  WHERE CAST(date as VARCHAR) LIKE :dateSoiree)                
                                ');     //selectionne ls valeurs des specatcle dont soiree à la bonne date

        $stmt->execute([
            'dateSoiree' => $date->format('Y-m-d') .'%',
            'style' => '%'.$style.'%'
        ]);

        $spectacles = [];   

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {        //on crée le tableau de spectacle
            $spectacles[] =  $this->versEntite($row);
        }

        return $spectacles; //renvoie un tableau de Spectacle

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
