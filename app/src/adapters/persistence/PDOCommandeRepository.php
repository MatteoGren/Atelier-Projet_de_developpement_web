<?php

declare(strict_types=1);

namespace nrv\adapters\persistence;

use nrv\application\ports\spi\CommandeRepositoryInterface;
use nrv\domain\entities\Commande;
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
}
