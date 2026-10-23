# NRV.net - Billetterie du festival Nancy Rock Vibration

API RESTful (PHP / Slim, architecture hexagonale) et front HTML/SCSS/JS.

## Installation

1. Créer le fichier `nrv.env` à la racine :

   ```
   POSTGRES_DB=nrv
   POSTGRES_USER=nrv
   POSTGRES_PASSWORD=un_mot_de_passe
   JWT_SECRET=une_longue_chaine_aleatoire
   ```

   `JWT_SECRET` signe les jetons de connexion (au moins 32 caractères, par exemple `openssl rand -hex 32`) ; l'API refuse de démarrer sans lui. `CORS_ORIGINS` (facultatif) liste les adresses du front autorisées à appeler l'API, séparées par des virgules ; par défaut `http://localhost:31000`, `http://127.0.0.1:31000` et `http://docketu.iutnc.univ-lorraine.fr:31000`.

2. Lancer les conteneurs :

   ```bash
   docker compose up -d
   ```

3. Créer les tables et charger le jeu de données de test :

   ```bash
   docker compose exec nrv.db sh -c 'psql -U "$POSTGRES_USER" -d "$POSTGRES_DB" -f /var/sql/nrv.schema.sql'
   docker compose exec nrv.db sh -c 'psql -U "$POSTGRES_USER" -d "$POSTGRES_DB" -f /var/sql/nrv.donnees.sql'
   ```

Ces deux commandes peuvent être relancées sans risque : le schéma supprime les tables existantes avant de les recréer.

## Fonctionnalités et répartition des tâches

| N° | Fonctionnalité | Réalisé par |
|----|----------------|-------------|
| | **Programme** | |
| 1 | Liste des spectacles (titre, date, horaire, image) | Serge |
| 2 | Détail d'une soirée (nom, thématique, date, horaire, lieu, tarifs, spectacles avec titre, artistes, description, style et vidéo) | Serge |
| 3 | Clic sur un spectacle, qui affiche la soirée correspondante | Serge |
| 4 | Filtre par date | Kazel |
| 5 | Filtre par style de musique | Kazel |
| | **Achat de billets** | |
| 6 | Ajout de billets d'une soirée dans un panier | Auriane |
| 7 | Consultation du panier avec calcul du montant total | Auriane |
| 8 | Validation du panier, qui devient une commande | Auriane |
| 9 | Paiement simulé (numéro, date d'expiration, code) : contrôle des places disponibles, mise à jour du stock et validation de la commande | Serge |
| 10 | Création des billets (nom de l'acheteur, référence, date et horaire de la soirée, catégorie de tarif), imprimables avec `@media print` | Serge |
| | **Connexion / inscription** | |
| 11 | Création de comptes utilisateur (email, mot de passe) | Mattéo |
| 12 | Connexion aux services après la création d'un compte | Mattéo |
| | **Backoffice** | |
| 13 | Jauge par soirée (places vendues) et recette de la soirée | Kazel (back), Auriane (front) |
| | **Fonctionnalités étendues** | |
| 14 | Pagination de la liste des spectacles | Kazel |
| 15 | Modification du panier (nombre de billets) | Auriane |
| 16 | Ajout de spectacles et de soirées depuis le backoffice | Mattéo |

Autres tâches :

- **Serge** : wireframes, déploiement sur docketu.
- **Auriane** : modification du front.

## Services

| Service | URL |
|---------|-----|
| Front | http://localhost:31000 |
| API | http://localhost:31001 |
| Adminer | http://localhost:31002 (serveur : `nrv.db`) |

La base Postgres n'a pas de port publié : seuls l'API et Adminer y accèdent, via le réseau Docker.

En déploiement sur docketu, remplacer `localhost` par `docketu.iutnc.univ-lorraine.fr`.

## Déploiement sur docketu

| Service | URL |
|---------|-----|
| Front | http://docketu.iutnc.univ-lorraine.fr:31000 |
| API | http://docketu.iutnc.univ-lorraine.fr:31001 |
| Adminer | http://docketu.iutnc.univ-lorraine.fr:31002 (serveur : `nrv.db`) |

### Connexion au VPN de l'université

docketu n'est joignable que depuis le réseau de l'université : hors de l'IUT, il faut se connecter au VPN avant toute connexion SSH ou ouverture des URL ci-dessus.

1. Installer **Cisco Secure Client** (anciennement Cisco AnyConnect). Il se télécharge en ouvrant `https://vpn.lothaire.net` dans un navigateur et en se connectant avec ses identifiants universitaires. Sous Linux, `openconnect` fonctionne aussi : `sudo openconnect vpn.lothaire.net`.
2. Lancer Cisco Secure Client, saisir l'adresse `vpn.lothaire.net`, puis cliquer sur « Connecter ».
3. S'identifier avec son compte de l'Université de Lorraine (le même identifiant et mot de passe que pour l'ENT).
4. Une fois connecté, se connecter à docketu en SSH avec ce même identifiant : `ssh identifiant@docketu.iutnc.univ-lorraine.fr`.

Penser à se déconnecter du VPN une fois le travail terminé.

### Déploiement

Les ports 31000-31002 ne peuvent être utilisés que par un seul compte à la fois : avant de déployer sur un autre compte, arrêter le déploiement existant (`docker compose down`).

Premier déploiement :

```bash
ssh e2XXXXXu@docketu.iutnc.univ-lorraine.fr
git clone https://github.com/MatteoGren/Atelier-Projet_de_developpement_web.git ~/nrv
cd ~/nrv
cat > nrv.env <<EOF
POSTGRES_DB=nrv
POSTGRES_USER=nrv
POSTGRES_PASSWORD=$(openssl rand -hex 16)
JWT_SECRET=$(openssl rand -hex 32)
EOF
chmod 600 nrv.env
docker compose up -d
docker compose logs -f api.nrv   # attendre "Development Server ... started", puis Ctrl+C
docker compose exec nrv.db sh -c 'psql -U "$POSTGRES_USER" -d "$POSTGRES_DB" -f /var/sql/nrv.schema.sql'
docker compose exec nrv.db sh -c 'psql -U "$POSTGRES_USER" -d "$POSTGRES_DB" -f /var/sql/nrv.donnees.sql'
```

- Adminer est lancé en IPv4 (`command` dans `docker-compose.yml`) car le Docker de docketu n'a pas d'IPv6.
- `docker compose down` supprime les données de la base : recharger ensuite les deux fichiers SQL.

## Routes de l'API

| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/` | Point d'entrée : liens vers toutes les ressources de l'API |
| GET | `/spectacles` | Liste des spectacles (titre, date, horaire, image) |
| GET | `/spectacles?page=N` | Page N du programme (6 spectacles) avec liens `first`/`last`/`prev`/`next` ; 400 si N est invalide, 404 si la page n'existe pas |
| GET | `/spectacles?date=AAAA-MM-JJ&style=...` | Spectacles filtrés par date et/ou style ; 400 si la date est invalide |
| POST | `/spectacles` | Crée un spectacle (organisateur ou administrateur) |
| GET | `/soirees` | Soirées avec places vendues, capacité et recette (tableau de bord) |
| POST | `/soirees` | Crée une soirée de 2 ou 3 spectacles (organisateur ou administrateur) |
| GET | `/soirees/{id}` | Détail d'une soirée (nom, thématique, date, horaire, lieu, tarifs, spectacles) ; 400 si l'identifiant n'est pas un UUID, 404 si la soirée n'existe pas |
| POST | `/commandes` | Crée une commande en attente à partir du panier (connexion requise) |
| GET | `/commandes/{id}` | Détail d'une commande de l'utilisateur connecté |
| POST | `/commandes/{id}/paiement` | Paiement simulé (16 chiffres, date MM/AA non expirée, code à 3 chiffres) ; 409 si plus assez de places |
| GET | `/commandes/{id}/billets` | Billets d'une commande payée ; 409 si elle n'est pas encore payée |
| GET | `/billets` | Tous les billets payés de l'utilisateur connecté |
| GET | `/billets/{token}` | Un billet par son lien personnel (public) |
| POST | `/utilisateurs` | Inscription ; 409 si l'email a déjà un compte |
| POST | `/sessions` | Connexion : renvoie l'utilisateur et un jeton JWT (`token`, valable 4 h) ; 401 si l'email ou le mot de passe est incorrect |

Les routes marquées « connexion requise » ou liées à un utilisateur attendent l'en-tête `Authorization: Bearer <token>` avec le jeton reçu à la connexion ; sans jeton valide (absent, falsifié ou expiré), elles répondent 401.

## Tests unitaires

```bash
docker compose exec api.nrv vendor/bin/pest
```

## Front : compilation SCSS

Le fichier `front/css/style.css` compilé est versionné : Node/npm n'est nécessaire que pour modifier le SCSS.

```bash
cd front
npm install
npm run sass
```
