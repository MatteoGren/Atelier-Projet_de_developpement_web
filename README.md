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

## Tests unitaires

```bash
docker compose exec api.nrv vendor/bin/phpunit
```

## Front : compilation SCSS

```bash
cd front
npm install
npm run sass
```
