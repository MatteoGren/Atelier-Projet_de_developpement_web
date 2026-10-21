<?php

declare(strict_types=1);

namespace nrv\application\usecases;

use nrv\application\dtos\CommandeDTO;
use nrv\application\dtos\CreerCommandeDTO;
use nrv\application\exceptions\SoireeIntrouvableException;
use nrv\application\ports\api\ServiceCommandeInterface;
use nrv\application\ports\spi\CommandeRepositoryInterface;
use nrv\application\ports\spi\SoireeRepositoryInterface;
use nrv\application\validators\CommandeValidator;
use nrv\domain\entities\Billet;
use nrv\domain\entities\Commande;
use nrv\domain\entities\Soiree;
use Ramsey\Uuid\Uuid;


class ServiceCommande implements ServiceCommandeInterface
{

    private SoireeRepositoryInterface $soireeRepository;
    private CommandeRepositoryInterface $commandeRepository;

    public function __construct(SoireeRepositoryInterface $soireeRepository, CommandeRepositoryInterface $commandeRepository
    ) {
        $this->soireeRepository = $soireeRepository;
        $this->commandeRepository = $commandeRepository;
    }

    public function creerCommande(CreerCommandeDTO $dto): CommandeDTO
    {
        CommandeValidator::valider($dto);             

        $commande = new Commande(Uuid::uuid4()->toString(), $dto->idUtilisateur);

        foreach ($dto->lignes as $ligne) {              
            $soiree = $this->soireeRepository->findById($ligne->idSoiree);
            if ($soiree === null) {
                throw new SoireeIntrouvableException($ligne->idSoiree);
            }
            for ($i = 0; $i < $ligne->nbNormal; $i++) {
                $commande->ajouterBillet($this->nouveauBillet($commande, $soiree, false));
            }
            for ($i = 0; $i < $ligne->nbReduit; $i++) {
                $commande->ajouterBillet($this->nouveauBillet($commande, $soiree, true));
            }
        }

        $this->commandeRepository->save($commande);   

        return new CommandeDTO(
            $commande->getIdCommande(),
            $commande->getStatut(),
            $commande->getMontantTotal(),
            count($commande->getBillets())
        );
    }

    private function nouveauBillet(Commande $commande, Soiree $soiree, bool $tarifReduit): Billet
    {
        $tarifs = $soiree->getTarifs();

        return new Billet(
            Uuid::uuid4()->toString(),
            $commande->getIdCommande(),
            $soiree->getId(),
            'NRV-' . strtoupper(bin2hex(random_bytes(4))),   
            bin2hex(random_bytes(32)),                       
            $tarifReduit,
            $tarifReduit ? $tarifs['reduit'] : $tarifs['normal']
        );
    }
}
