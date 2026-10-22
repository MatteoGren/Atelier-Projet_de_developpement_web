<?php

declare(strict_types=1);

namespace nrv\application\ports\api;

use nrv\application\dtos\BilletDTO;
use nrv\application\exceptions\BilletIntrouvableException;
use nrv\application\exceptions\CommandeIntrouvableException;
use nrv\application\exceptions\IdentifiantInvalideException;
use nrv\application\exceptions\SoireeIntrouvableException;
use nrv\application\exceptions\UtilisateurIntrouvableException;
use nrv\domain\exceptions\CommandeNonPayeeException;

interface ServiceBilletInterface
{
    /**
     * @return BilletDTO[]
     * @throws IdentifiantInvalideException
     * @throws CommandeIntrouvableException
     * @throws CommandeNonPayeeException
     * @throws UtilisateurIntrouvableException
     * @throws SoireeIntrouvableException
     */
    public function afficherBilletsCommande(string $idCommande, string $idUtilisateur): array;

    /**
     * @return BilletDTO[] billets payés de l'utilisateur, toutes commandes confondues
     * @throws UtilisateurIntrouvableException
     * @throws SoireeIntrouvableException
     */
    public function afficherBilletsUtilisateur(string $idUtilisateur): array;

    /**
     * @throws BilletIntrouvableException
     * @throws UtilisateurIntrouvableException
     * @throws SoireeIntrouvableException
     */
    public function afficherBillet(string $token): BilletDTO;
}
