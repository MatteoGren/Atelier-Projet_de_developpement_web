DROP TABLE IF EXISTS billet CASCADE;
DROP TABLE IF EXISTS commande CASCADE;
DROP TABLE IF EXISTS spectacle CASCADE;
DROP TABLE IF EXISTS soiree CASCADE;
DROP TABLE IF EXISTS utilisateur CASCADE;

-- Utilisateurs
CREATE TABLE utilisateur (
    idUtilisateur            UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    nom           VARCHAR(100) NOT NULL,
    prenom        VARCHAR(100) NOT NULL,
    email         VARCHAR(255) NOT NULL UNIQUE,
    telephone     VARCHAR(20),
    mdp           VARCHAR(255) NOT NULL,          -- hash (password_hash)
    role          VARCHAR(20)  NOT NULL DEFAULT 'client'
                  CHECK (role IN ('client', 'organisateur', 'administrateur'))
);

-- Soirées
CREATE TABLE soiree (
    id            UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    nom           VARCHAR(150) NOT NULL,
    thematique    VARCHAR(100) NOT NULL,
    date          TIMESTAMP    NOT NULL,           -- date + horaire de début
    lieu          VARCHAR(150) NOT NULL,
    capacite      INT          NOT NULL CHECK (capacite > 0)
);

-- Spectacles
CREATE TABLE spectacle (
    id             UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    soiree_id      UUID NOT NULL REFERENCES soiree(id) ON DELETE CASCADE,
    style          VARCHAR(50) NOT NULL,          
    titre          VARCHAR(150) NOT NULL,
    artistes       VARCHAR(255) NOT NULL,           -- ex : 'Blue Meurthe, Mama Delta'
    description    TEXT,
    chemin_images  TEXT,                            -- plusieurs URLs séparées par ';'
    chemin_video   VARCHAR(255),
    horaire        TIMESTAMP NOT NULL               -- horaire prévisionnel
);

-- Commandes (le panier validé devient une commande)
CREATE TABLE commande (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    utilisateur_id  UUID NOT NULL REFERENCES utilisateur(idUtilisateur) ON DELETE CASCADE,
    statut          VARCHAR(20) NOT NULL DEFAULT 'en_attente'
                    CHECK (statut IN ('en_attente', 'validee', 'annulee')),
    montant_total   NUMERIC(10,2) NOT NULL DEFAULT 0 CHECK (montant_total >= 0),
    created_at      TIMESTAMP NOT NULL DEFAULT now(),
    paid_at         TIMESTAMP
);

CREATE INDEX idx_commande_utilisateur ON commande(utilisateur_id);

-- Billets
CREATE TABLE billet (
    id            UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    commande_id   UUID NOT NULL REFERENCES commande(id) ON DELETE CASCADE,
    soiree_id     UUID NOT NULL REFERENCES soiree(id),
    ref           VARCHAR(30) NOT NULL UNIQUE,
    token         VARCHAR(64) NOT NULL UNIQUE,     -- token aléatoire pour l'URL
    tarif_reduit  BOOLEAN NOT NULL DEFAULT FALSE,
    prix          NUMERIC(8,2) NOT NULL CHECK (prix >= 0)  -- prix figé à l'achat
);

CREATE INDEX idx_billet_commande ON billet(commande_id);
CREATE INDEX idx_billet_soiree   ON billet(soiree_id);