import { getSoiree } from './api.js';
import { detailSoiree } from './templates.js';
import { ajouterBillets } from './panier.js';

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

        document.querySelector('#btn-ajouter').addEventListener('click', () => {
        const ok = ajouterBillets(
            soiree,                                    
            document.querySelector('#nb-normal').value,
            document.querySelector('#nb-reduit').value
        );
        document.querySelector('#msg-panier').textContent =
            ok ? 'Billets ajoutés au panier.' : 'Choisissez au moins un billet.';
        });


    } catch (erreur) {
        message.textContent = 'Impossible de charger la soirée. Réessayez plus tard.';
        console.error(erreur);
    }
}

afficherSoiree();
