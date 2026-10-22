<?php

declare(strict_types=1);

namespace nrv\application\usecases;

use DateTimeImmutable;
use nrv\application\dtos\CommandeDTO;
use nrv\application\dtos\CreerCommandeDTO;
use nrv\application\dtos\PaiementDTO;
use nrv\application\exceptions\CommandeIntrouvableException;
use nrv\application\exceptions\SoireeIntrouvableException;
use nrv\application\ports\api\ServiceCommandeInterface;
use nrv\application\ports\spi\CommandeRepositoryInterface;
use nrv\application\ports\spi\SoireeRepositoryInterface;
use nrv\application\validators\CommandeValidator;
use nrv\application\validators\IdentifiantValidator;
use nrv\application\validators\PaiementValidator;
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

        return CommandeDTO::fromEntity($commande);
    }

    public function afficherCommande(string $idCommande, string $idUtilisateur): CommandeDTO
    {
        IdentifiantValidator::valider($idCommande);

        return CommandeDTO::fromEntity($this->trouverCommande($idCommande, $idUtilisateur));
    }

    public function payerCommande(PaiementDTO $paiement): CommandeDTO
    {
        PaiementValidator::valider($paiement);

        $commande = $this->trouverCommande($paiement->idCommande, $paiement->idUtilisateur);
        $commande->verifierPayable();

        // Contrôle des places au moment du paiement (et non à l'ajout au panier).
        foreach ($commande->billetsParSoiree() as $idSoiree => $nbBillets) {
            $soiree = $this->soireeRepository->findById($idSoiree);
            if ($soiree === null) {
                throw new SoireeIntrouvableException($idSoiree);
            }
            $soiree->verifierPlaces($nbBillets);
            
            $soiree->setPlacesVendues($nbBillets);
            $this->soireeRepository->addBillet($soiree, $nbBillets);  //ajout du billet apres le paiement et la verification des places
        }

        $commande->payer(new DateTimeImmutable());
        $this->commandeRepository->enregistrerPaiement($commande);

        return CommandeDTO::fromEntity($commande);
    }

    // La commande d'un autre utilisateur est traitée comme introuvable : on ne révèle pas qu'elle existe.
    private function trouverCommande(string $idCommande, string $idUtilisateur): Commande
    {
        $commande = $this->commandeRepository->findById($idCommande);
        if ($commande === null || $commande->getIdUtilisateur() !== $idUtilisateur) {
            throw new CommandeIntrouvableException($idCommande);
        }
        return $commande;
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
