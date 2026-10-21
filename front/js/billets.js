import { getBillet, getBilletsCommande } from './api.js';
import { displayBillets } from './ui.js';

const message = document.getElementById('message');
const parametres = new URLSearchParams(window.location.search);
const idCommande = parametres.get('commande');
const token = parametres.get('token');

// Deux accès : tous les billets d'une commande, ou un seul billet par son lien personnel.
async function chargerBillets() {
    if (idCommande) {
        const billets = await getBilletsCommande(idCommande);
        return billets === null ? 'Cette commande n\'existe pas.' : billets;
    }
    if (token) {
        const billet = await getBillet(token);
        return billet === null ? 'Ce billet n\'existe pas.' : [billet];
    }
    return 'Aucun billet à afficher.';
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
        // Seuls les billets d'une commande demandent d'être connecté ; le lien personnel reste public.
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
