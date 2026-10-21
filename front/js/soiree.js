import { getSoiree } from './api.js';
import { ajouterBillets } from './panier.js';
import { displaySoiree } from './ui.js';

const message = document.getElementById('message');

function afficherSoiree(soiree) {
    displaySoiree(soiree);
    document.title = `NRV.net - ${soiree.nom}`;

    document.querySelector('#btn-ajouter').addEventListener('click', () => {
        const ok = ajouterBillets(
            soiree,
            document.querySelector('#nb-normal').value,
            document.querySelector('#nb-reduit').value
        );
        document.querySelector('#msg-panier').textContent =
            ok ? 'Billets ajoutés au panier.' : 'Choisissez au moins un billet.';
    });
}

async function chargerSoiree() {
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
        afficherSoiree(soiree);
        message.textContent = '';
    } catch (erreur) {
        message.textContent = 'Impossible de charger la soirée. Réessayez plus tard.';
        console.error(erreur);
    }
}

chargerSoiree();