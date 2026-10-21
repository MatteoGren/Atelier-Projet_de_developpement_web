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

    public function findByEmail(string $email): ?Utilisateur {
        $stmt = $this->pdo->prepare('SELECT * FROM utilisateur WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new Utilisateur(
            $row['idutilisateur'],
            $row['nom'],
            $row['prenom'],
            $row['email'],
            $row['telephone'],
            $row['mdp'],
            $row['role']
        );
    }

    public function findById(string $id): ?Utilisateur {
        $stmt = $this->pdo->prepare('SELECT * FROM utilisateur WHERE idUtilisateur = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new Utilisateur(
            $row['idutilisateur'],
            $row['nom'],
            $row['prenom'],
            $row['email'],
            $row['telephone'] ?? '', // le téléphone est facultatif en base
            $row['mdp'],
            $row['role']
        );
    }


    public function save(Utilisateur $utilisateur): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO utilisateur (idUtilisateur, nom, prenom, email, telephone, mdp, role) 
             VALUES (:id, :nom, :prenom, :email, :telephone, :mdp, :role)'
        );

        $stmt->execute([
            'id' => $utilisateur->getIdUtilisateur(),
            'nom' => $utilisateur->getNom(),
            'prenom' => $utilisateur->getPrenom(),
            'email' => $utilisateur->getEmail(),
            'telephone' => $utilisateur->getTelephone(),
            'mdp' => $utilisateur->getMotDePasse(), // Le mot de passe doit déjà être haché par le service !
            'role' => $utilisateur->getRole()
        ]);
    }



}