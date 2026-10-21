<?php

declare(strict_types=1);

namespace nrv\application\ports\api;

use nrv\domain\entities\Billet;
use nrv\domain\entities\Commande;
use nrv\domain\entities\Soiree;
use nrv\application\dtos\CommandeDTO;
use nrv\application\dtos\CreerCommandeDTO;
use nrv\application\dtos\PaiementDTO;
use nrv\application\exceptions\CommandeIntrouvableException;
use nrv\application\exceptions\CommandeInvalideException;
use nrv\application\exceptions\IdentifiantInvalideException;
use nrv\application\exceptions\PaiementInvalideException;
use nrv\application\exceptions\SoireeIntrouvableException;
use nrv\domain\exceptions\CommandeNonPayableException;
use nrv\domain\exceptions\PlacesInsuffisantesException;

interface ServiceCommandeInterface
{
    public function creerCommande(CreerCommandeDTO $commande): CommandeDTO;

    /**
     * @throws IdentifiantInvalideException
     * @throws CommandeIntrouvableException
     */
    public function afficherCommande(string $idCommande, string $idUtilisateur): CommandeDTO;

    /**
     * @throws IdentifiantInvalideException
     * @throws PaiementInvalideException
     * @throws CommandeIntrouvableException
     * @throws CommandeNonPayableException
     * @throws PlacesInsuffisantesException
     */
    public function payerCommande(PaiementDTO $paiement): CommandeDTO;
}
