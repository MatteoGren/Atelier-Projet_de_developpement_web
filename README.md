# NRV.net - Billetterie du festival Nancy Rock Vibration

API RESTful (PHP / Slim, architecture hexagonale) et front HTML/SCSS/JS.

## Installation

1. Créer le fichier `nrv.env` à la racine :

   ```
   POSTGRES_DB=nrv
   POSTGRES_USER=nrv
   POSTGRES_PASSWORD=un_mot_de_passe
   ```

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

## Mise à jour après un `git pull`

```bash
docker compose up -d
docker compose restart api.nrv
```

- `up -d` recrée les conteneurs dont la configuration a changé et lance les nouveaux services.
- `restart api.nrv` relance `composer install`, ce qui régénère l'autoload. À faire si l'API affiche `Class "..." not found` (nouvelle classe ou nouveau namespace).
- Si les fichiers SQL ont changé, relancer les deux commandes de l'étape 3.

## Services

| Service | URL |
|---------|-----|
| Front | http://localhost:1012 |
| API | http://localhost:1010 |
| Adminer | http://localhost:1011 |

## Routes de l'API

| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/spectacles` | Liste des spectacles (titre, date, horaire, image) |
| GET | `/soirees/{id}` | Détail d'une soirée (nom, thématique, date, horaire, lieu, tarifs, spectacles) ; 400 si l'identifiant n'est pas un UUID, 404 si la soirée n'existe pas |

## Tests unitaires

```bash
docker compose exec api.nrv vendor/bin/phpunit
```

## Front : compilation SCSS

Le fichier `front/css/style.css` compilé est versionné : Node/npm n'est nécessaire que pour modifier le SCSS.

```bash
cd front
npm install
npm run sass
```
