
import { getStatistiquesVentes } from './api.js';
import { displayTableauBord } from './ui.js';

const elements = {
    grille: document.getElementById('grille-soirees'),
    actualiser: document.getElementById('btn-actualiser'),
    etat: document.getElementById('etat-chargement')
};

async function chargerTableauBord() {
    elements.actualiser.disabled = true;
    elements.etat.textContent = 'Chargement des statistiques...';
    elements.etat.classList.remove('tableau-bord-message--erreur');

    try {
        const donnees = await getStatistiquesVentes();

        if (!Array.isArray(donnees.soiree)) {
            throw new Error('Format de réponse API invalide.');
        }

        const soirees = donnees.soiree.map((soiree) => ({
            nom: String(soiree.nom ?? 'Soirée sans nom'),
            placesVendues: Number(soiree.placesVendues),
            capacite: Number(soiree.capacite),
            recette: Number(soiree.recette)
        }));

        if (soirees.some((soiree) =>
            !Number.isFinite(soiree.placesVendues) ||
            !Number.isFinite(soiree.capacite) ||
            !Number.isFinite(soiree.recette) ||
            soiree.placesVendues < 0 ||
            soiree.capacite < 0 ||
            soiree.recette < 0
        )) {
            throw new Error('Une statistique contient une valeur invalide.');
        }

        const totalPlaces = soirees.reduce(
            (total, soiree) => total + soiree.placesVendues, 0
        );

        const capaciteTotale = soirees.reduce(
            (total, soiree) => total + soiree.capacite, 0
        );

        const recetteTotale = soirees.reduce(
            (total, soiree) => total + soiree.recette, 0
        );

        displayTableauBord({
            soirees,
            totalPlaces,
            capaciteTotale,
            recetteTotale
        });

        elements.etat.textContent = 'Statistiques mises à jour.';
    } catch (erreur) {
        console.error(erreur);

        elements.etat.textContent =
            'Impossible de charger les statistiques. Vérifie la connexion à l\'API.';

        elements.etat.classList.add('tableau-bord-message--erreur');
        elements.grille.replaceChildren();
    } finally {
        elements.actualiser.disabled = false;
    }
}

elements.actualiser.addEventListener('click', chargerTableauBord);

chargerTableauBord();