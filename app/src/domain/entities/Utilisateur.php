<?php

namespace Atelier\domain\entities;


class Utilisateur {

    public function __construct(
        private int $id,
        private string $nom,
        private string $prenom,
        private string $email,
        private string $motDePasse
    ) {}

    


}