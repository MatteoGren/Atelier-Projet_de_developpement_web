<?php

namespace nrv\adapters\persistence;

use nrv\application\ports\spi\UtilisateurRepositoryInterface;
use PDO;
use nrv\domain\entities\Utilisateur;

class UtilisateurRepository implements UtilisateurRepositoryInterface {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function FindByEmail(string $email): ?Utilisateur {
        $stmt = $this->pdo->prepare('SELECT * FROM utilisateurs WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new Utilisateur(
            $row['idUtilisateur'],
            $row['nom'],
            $row['prenom'],
            $row['email'],
            $row['telephone'],
            $row['motDePasse'],
            $row['role']
        );
    }


    public function save(Utilisateur $utilisateur): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO utilisateurs (idUtilisateur, nom, prenom, email, telephone, motDePasse, role) 
             VALUES (:id, :nom, :prenom, :email, :telephone, :motDePasse, :role)'
        );

        $stmt->execute([
            'id' => $utilisateur->getIdUtilisateur(),
            'nom' => $utilisateur->getNom(),
            'prenom' => $utilisateur->getPrenom(),
            'email' => $utilisateur->getEmail(),
            'telephone' => $utilisateur->getTelephone(),
            'motDePasse' => $utilisateur->getMotDePasse(), // Le mot de passe doit déjà être haché par le service !
            'role' => $utilisateur->getRole()
        ]);
    }



}