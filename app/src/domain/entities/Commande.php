<?php

namespace nrv\domain\entities;



class Commande {

    public function __construct(
        private string $idCommande,
        private string $idUtilisateur,
        private string $StatutCommande,
        private float $montantTotal,
        private DateTime $dateCommande,
        private DateTime $datePaiement,
    ) {}



}