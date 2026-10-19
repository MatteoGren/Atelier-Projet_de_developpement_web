<?php

namespace nrv\domain\entities;


class Utilisateur {

    public function __construct(
        private string $idUtilisateur,
        private string $nom,
        private string $prenom,
        private string $email,
        private string $telephone,
        private string $motDePasse,
        private string $role
    ) {}

    


}