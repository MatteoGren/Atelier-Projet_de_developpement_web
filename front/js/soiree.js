import { getSoiree } from './api.js';
import { detailSoiree } from './templates.js';

const conteneur = document.getElementById('detail-soiree');
const message = document.getElementById('message');

async function afficherSoiree() {
    const id = new URLSearchParams(window.location.search).get('id');
    if (!id) {
        message.textContent = 'Aucune soirée demandée.';
        return;
    }

    try {
        const soiree = await getSoiree(id);
        if (soiree === null) {
            message.textContent = 'Cette soirée n\'existe pas.';
            return;
        }
        conteneur.innerHTML = detailSoiree(soiree);
        document.title = `NRV.net - ${soiree.nom}`;
        message.textContent = '';
    } catch (erreur) {
        message.textContent = 'Impossible de charger la soirée. Réessayez plus tard.';
        console.error(erreur);
    }
}

afficherSoiree();
