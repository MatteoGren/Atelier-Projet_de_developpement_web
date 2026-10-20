<?php

declare(strict_types=1);

namespace nrv\application\ports\api;


interface ServiceUtilisateurInterface
{

    public function connecter(string $email, string $motDePasse): Utilisateur;

    

}
