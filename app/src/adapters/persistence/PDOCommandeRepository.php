<?php

declare(strict_types=1);

namespace nrv\adapters\persistence;

use DateTimeImmutable;
use nrv\application\ports\spi\CommandeRepositoryInterface;
use nrv\domain\entities\Billet;
use nrv\domain\entities\Commande;
use nrv\domain\exceptions\CommandeNonPayableException;
use nrv\domain\exceptions\PlacesInsuffisantesException;
use PDO;
use Throwable;

class PDOCommandeRepository implements CommandeRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(Commande $commande): void
    {
        $this->pdo->beginTransaction();

        try {
            $stmtCommande = $this->pdo->prepare(
                'INSERT INTO commande (id, utilisateur_id, statut, montant_total)
                 VALUES (:id, :utilisateur, :statut, :montant)'
            );
            $stmtCommande->execute([
                'id' => $commande->getIdCommande(),
                'utilisateur' => $commande->getIdUtilisateur(),
                'statut' => $commande->getStatut(),
                'montant' => $commande->getMontantTotal(),
            ]);

            $stmtBillet = $this->pdo->prepare(
                'INSERT INTO billet (id, commande_id, soiree_id, ref, token, tarif_reduit, prix)
                 VALUES (:id, :commande, :soiree, :ref, :token, :reduit, :prix)'
            );
            foreach ($commande->getBillets() as $billet) {
                $stmtBillet->bindValue('id', $billet->getIdBillet());
                $stmtBillet->bindValue('commande', $billet->getIdCommande());
                $stmtBillet->bindValue('soiree', $billet->getIdSoiree());
                $stmtBillet->bindValue('ref', $billet->getRef());
                $stmtBillet->bindValue('token', $billet->getToken());
                $stmtBillet->bindValue('reduit', $billet->isTarifReduit(), PDO::PARAM_BOOL);
                $stmtBillet->bindValue('prix', $billet->getPrix());
                $stmtBillet->execute();
            }

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function findById(string $id): ?Commande
    {
        $stmtCommande = $this->pdo->prepare(
            'SELECT id, utilisateur_id, statut, created_at, paid_at
             FROM commande
             WHERE id = :id'
        );
        $stmtCommande->execute(['id' => $id]);
        $ligne = $stmtCommande->fetch(PDO::FETCH_ASSOC);

        if ($ligne === false) {
            return null;
        }

        // Montant à 0 : il est recalculé par ajouterBillet() à partir des prix figés des billets.
        $commande = new Commande(
            $ligne['id'],
            $ligne['utilisateur_id'],
            $ligne['statut'],
            0.0,
            new DateTimeImmutable($ligne['created_at']),
            $ligne['paid_at'] === null ? null : new DateTimeImmutable($ligne['paid_at'])
        );

        $stmtBillets = $this->pdo->prepare(
            'SELECT id, commande_id, soiree_id, ref, token, tarif_reduit, prix
             FROM billet
             WHERE commande_id = :id
             ORDER BY ref'
        );
        $stmtBillets->execute(['id' => $id]);

        foreach ($stmtBillets->fetchAll(PDO::FETCH_ASSOC) as $billet) {
            $commande->ajouterBillet(new Billet(
                $billet['id'],
                $billet['commande_id'],
                $billet['soiree_id'],
                $billet['ref'],
                $billet['token'],
                (bool) $billet['tarif_reduit'],
                (float) $billet['prix']
            ));
        }

        return $commande;
    }

    public function enregistrerPaiement(Commande $commande): void
    {
        $this->pdo->beginTransaction();

        try {
            // La condition dans le WHERE empêche deux paiements simultanés de dépasser la capacité.
            $stmtPlaces = $this->pdo->prepare(
                'UPDATE soiree
                 SET places_vendues = places_vendues + :nb
                 WHERE id = :soiree AND places_vendues + :nb <= capacite'
            );
            foreach ($commande->billetsParSoiree() as $idSoiree => $nbBillets) {
                $stmtPlaces->execute(['nb' => $nbBillets, 'soiree' => $idSoiree]);
                if ($stmtPlaces->rowCount() === 0) {
                    throw new PlacesInsuffisantesException($idSoiree);
                }
            }

            $stmtCommande = $this->pdo->prepare(
                'UPDATE commande
                 SET statut = :statut, paid_at = :date
                 WHERE id = :id AND statut = :enAttente'
            );
            $stmtCommande->execute([
                'statut' => $commande->getStatut(),
                'date' => $commande->getDatePaiement()->format('Y-m-d H:i:s'),
                'id' => $commande->getIdCommande(),
                'enAttente' => Commande::STATUT_EN_ATTENTE,
            ]);
            if ($stmtCommande->rowCount() === 0) {
                throw new CommandeNonPayableException($commande->getIdCommande());
            }

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
