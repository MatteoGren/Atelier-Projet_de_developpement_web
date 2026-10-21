<?php

declare(strict_types=1);

namespace nrv\application\ports\spi;

use nrv\domain\entities\Commande;
use nrv\domain\exceptions\CommandeNonPayableException;
use nrv\domain\exceptions\PlacesInsuffisantesException;

interface CommandeRepositoryInterface
{
    public function save(Commande $commande): void;

    /**
     * @return Commande|null null si aucune commande n'a cet identifiant
     */
    public function findById(string $id): ?Commande;

    /**
     * Enregistre une commande payée et ajoute ses billets aux places vendues de chaque soirée.
     *
     * @throws PlacesInsuffisantesException
     * @throws CommandeNonPayableException
     */
    public function enregistrerPaiement(Commande $commande): void;
}
