<?php

declare(strict_types=1);

namespace nrv\application\ports\api;

use nrv\domain\entities\Billet;
use nrv\domain\entities\Commande;
use nrv\domain\entities\Soiree;
use nrv\application\dtos\CommandeDTO;
use nrv\application\dtos\CreerCommandeDTO;
use nrv\application\exceptions\CommandeInvalideException;
use nrv\application\exceptions\IdentifiantInvalideException;
use nrv\application\exceptions\SoireeIntrouvableException;

interface ServiceCommandeInterface
{
    public function creerCommande(CreerCommandeDTO $commande): CommandeDTO;

}
