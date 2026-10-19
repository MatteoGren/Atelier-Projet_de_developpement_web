import { getSpectacles } from './api.js';
import { listeSpectacles, IMAGE_PAR_DEFAUT } from './templates.js';

const conteneur = document.getElementById('liste-spectacles');
const message = document.getElementById('message');

// Image absente ou cassée : on affiche l'image par défaut (l'évènement error ne remonte pas, d'où la capture).
conteneur.addEventListener('error', (evenement) => {
    const image = evenement.target;
    if (image.tagName === 'IMG' && !image.src.endsWith(IMAGE_PAR_DEFAUT)) {
        image.src = IMAGE_PAR_DEFAUT;
    }
}, true);

async function afficherSpectacles() {
    try {
        const spectacles = await getSpectacles();
        if (spectacles.length === 0) {
            message.textContent = 'Aucun spectacle programmé pour le moment.';
            return;
        }
        conteneur.innerHTML = listeSpectacles(spectacles);
        message.textContent = `${spectacles.length} spectacles au programme`;
    } catch (erreur) {
        message.textContent = 'Impossible de charger les spectacles. Réessayez plus tard.';
        console.error(erreur);
    }
}

afficherSpectacles();
