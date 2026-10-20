<?php

declare(strict_types=1);

namespace nrv\application\usecases;

use nrv\application\ports\api\ServiceUtilisateurInterface;


class ServiceUtilisateur implements ServiceUtilisateurInterface
{
    
    private UtilisateurRepositoryInterface $utilisateurRepository;


    public function __construct(UtilisateurRepositoryInterface $utilisateurRepository)
    {
        $this->utilisateurRepository = $utilisateurRepository;
    }

    public function connecter(string $email, string $motDePasse): Utilisateur {
        $utilisateur = $this->utilisateurRepository->FindByEmail($email);
        if (!$utilisateur) {
            throw new UtilisateurIntrouvableException('Utilisateur non trouvé');
        }

        if (!password_verify($motDePasse, $utilisateur->getMotDePasse())) {
            throw new MotDePasseInvalideException('Mot de passe incorrect');
        }
        return $utilisateur;
    }




    public function register(string $nom, string $prenom, string $email, string $telephone, string $motDePasse, string $role): void {
        $existant = $this->utilisateurRepository->FindByEmail($email);
        if ($existant) {
            throw new \InvalidArgumentException("Un compte existe déjà avec cet email : $email");
        }

        $motDePasseHache = password_hash($motDePasse, PASSWORD_DEFAULT);

        $idUtilisateur = uniqid('usr_', true);


        $nouvelUtilisateur = new Utilisateur(
            $idUtilisateur,
            $nom,
            $prenom,
            $email,
            $telephone,
            $motDePasseHache,
            $role
        );

        $this->utilisateurRepository->save($nouvelUtilisateur);
    }

}
