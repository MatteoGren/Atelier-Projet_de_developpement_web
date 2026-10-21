import { getSpectacles } from './api.js';
import { displaySpectacles, IMAGE_PAR_DEFAUT } from './ui.js';

const conteneur = document.getElementById('liste-spectacles');
const message = document.getElementById('message');

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

        displaySpectacles(spectacles);
        message.textContent = `${spectacles.length} spectacles prévus`;
    } catch (erreur) {
        message.textContent = 'Impossible de charger les spectacles. Réessayez plus tard.';
        console.error(erreur);
    }
}

chargerSpectacles();
