import { getSpectacles, getSpectaclesFiltres } from './api.js';
import { displayFiltres, displaySpectacles, IMAGE_PAR_DEFAUT } from './ui.js';

const conteneur = document.getElementById('liste-spectacles');
const message = document.getElementById('message');
const formulaire = document.getElementById('filtres');

conteneur.addEventListener('error', (evenement) => {
    const image = evenement.target;
    if (image.tagName === 'IMG' && !image.src.endsWith(IMAGE_PAR_DEFAUT)) {
        image.src = IMAGE_PAR_DEFAUT;
    }
}, true);

async function chargerSpectacles() {
    try {
        const spectacles = await getSpectacles();
        if (spectacles.length === 0) {
            message.textContent = 'Aucun spectacle programmé pour le moment.';
            return;
        }

        displayFiltres(spectacles);
        displaySpectacles(spectacles);
        message.textContent = `${spectacles.length} spectacles prévus`;
    } catch (erreur) {
        message.textContent = 'Impossible de charger les spectacles. Réessayez plus tard.';
        console.error(erreur);
    }
}

async function filtrerSpectacles() {
    const date = formulaire.elements.date;
    const style = formulaire.elements.style;
    // Libellés affichés dans les listes, réutilisés pour résumer l'état du filtre.
    const etat = `État filtré : ${date.selectedOptions[0].text} · ${style.selectedOptions[0].text}`;

    message.textContent = 'Chargement des spectacles...';
    try {
        const spectacles = await getSpectaclesFiltres(date.value, style.value);
        displaySpectacles(spectacles);
        message.textContent = spectacles.length === 0
            ? `${etat} · aucun spectacle ne correspond`
            : `${etat} · ${spectacles.length} résultat${spectacles.length > 1 ? 's' : ''}`;
    } catch (erreur) {
        message.textContent = 'Impossible de filtrer les spectacles. Réessayez plus tard.';
        console.error(erreur);
    }
}

formulaire.addEventListener('submit', (evenement) => {
    evenement.preventDefault();
    filtrerSpectacles();
});

// Le reset remet les listes sur « Toutes les dates » / « Tous les styles » avant de recharger.
formulaire.addEventListener('reset', () => {
    setTimeout(filtrerSpectacles);
});

chargerSpectacles();
