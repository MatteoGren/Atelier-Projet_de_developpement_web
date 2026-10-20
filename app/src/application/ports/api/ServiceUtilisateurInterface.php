<?php

declare(strict_types=1);

namespace nrv\application\ports\api;

use nrv\domain\entities\Utilisateur;

interface ServiceUtilisateurInterface
{

    public function connecter(string $email, string $motDePasse): Utilisateur;

    public function register(string $nom, string $prenom, string $email, string $telephone, string $motDePasse, string $role): void;

    

}
