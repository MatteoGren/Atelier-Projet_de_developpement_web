import { getBillet, getBilletsCommande, getMesBillets } from './api.js';
import { displayBillets } from './ui.js';

const message = document.getElementById('message');
const parametres = new URLSearchParams(window.location.search);
const idCommande = parametres.get('commande');
const token = parametres.get('token');

// Trois accès : les billets d'une commande, un seul billet par son lien personnel, ou tous les billets de l'utilisateur.
async function chargerBillets() {
    if (idCommande) {
        const billets = await getBilletsCommande(idCommande);
        return billets === null ? 'Cette commande n\'existe pas.' : billets;
    }
    if (token) {
        const billet = await getBillet(token);
        return billet === null ? 'Ce billet n\'existe pas.' : [billet];
    }
    const billets = await getMesBillets();
    return billets.length === 0 ? 'Vous n\'avez encore aucun billet.' : billets;
}

async function afficherBillets() {
    try {
        const resultat = await chargerBillets();
        if (typeof resultat === 'string') {
            message.textContent = resultat;
            return;
        }
        displayBillets(resultat);
        document.querySelector('#btn-imprimer').hidden = false;
        message.textContent = '';
    } catch (erreur) {
        // Seul le lien personnel est public ; les deux autres accès demandent d'être connecté.
        if (erreur.status === 401) {
            window.location.href = 'auth.html';
            return;
        }
        message.textContent = erreur.message;
        console.error(erreur);
    }
}

document.querySelector('#btn-imprimer').addEventListener('click', () => window.print());

afficherBillets();
