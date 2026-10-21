import { getCommande, payerCommande } from './api.js';
import { displayConfirmation, displayPaiement } from './ui.js';

const message = document.getElementById('message');
const idCommande = new URLSearchParams(window.location.search).get('commande');

function afficherFormulaire(commande) {
    displayPaiement(commande);

    document.querySelector('#form-paiement').addEventListener('submit', async (evenement) => {
        evenement.preventDefault();
        const formulaire = evenement.currentTarget;
        const bouton = formulaire.querySelector('button[type="submit"]');
        const erreur = document.querySelector('#msg-paiement');
        bouton.disabled = true;
        erreur.textContent = '';

        try {
            const commandePayee = await payerCommande(idCommande, {
                // Les espaces tapés pour la lisibilité ne font pas partie du numéro.
                numeroCarte: formulaire.numeroCarte.value.replace(/\s/g, ''),
                dateExpiration: formulaire.dateExpiration.value.trim(),
                code: formulaire.code.value.trim()
            });
            displayConfirmation(commandePayee);
        } catch (e) {
            if (e.status === 401) {
                window.location.href = 'auth.html';
                return;
            }
            erreur.textContent = e.message;
            bouton.disabled = false;
        }
    });
}

async function chargerCommande() {
    if (!idCommande) {
        message.textContent = 'Aucune commande à payer.';
        return;
    }

    try {
        const commande = await getCommande(idCommande);
        if (commande === null) {
            message.textContent = 'Cette commande n\'existe pas.';
            return;
        }
        if (commande.statut === 'validee') {
            displayConfirmation(commande);
        } else if (commande.statut === 'en_attente') {
            afficherFormulaire(commande);
        } else {
            message.textContent = 'Cette commande a été annulée.';
            return;
        }
        message.textContent = '';
    } catch (erreur) {
        if (erreur.status === 401) {
            window.location.href = 'auth.html';
            return;
        }
        message.textContent = 'Impossible de charger la commande. Réessayez plus tard.';
        console.error(erreur);
    }
}

chargerCommande();
