import { displayPanier } from './ui.js';
import { API_URL } from './config.js'; 
import { validerPanier } from './api.js';

const CLE = 'nrv_panier';

// -------------------- Prix

function formatPrix(centimes) {
    return (centimes / 100).toFixed(2).replace('.', ',') + ' €';
}

function enCentimes(euros) {
    return Math.round(Number(euros) * 100);
}

// -------------------- Stockage

export function lirePanier() {
    const donnees = localStorage.getItem(CLE);

    if (donnees === null) {
        return [];
    }

    try {
        const panier = JSON.parse(donnees);
        return Array.isArray(panier) ? panier : [];
    } catch {
        return [];
    }
}

function ecrirePanier(panier) {
    localStorage.setItem(CLE, JSON.stringify(panier));
}

export function viderPanier() {
    localStorage.removeItem(CLE);
}

// -------------------- Actions

export function ajouterBillets(soiree, nbNormal, nbReduit) {
    const nbN = Number(nbNormal);
    const nbR = Number(nbReduit);

    if (!Number.isInteger(nbN) || !Number.isInteger(nbR)
        || nbN < 0 || nbR < 0 || nbN + nbR === 0) {
        return false;
    }

    const panier = lirePanier();
    let ligne = null;

    for (let i = 0; i < panier.length; i++) {
        if (String(panier[i].soireeId) === String(soiree.id)) {
            ligne = panier[i];
            break;
        }
    }

    if (ligne === null) {
        ligne = {
            soireeId: soiree.id,
            nom: soiree.nom,
            date: soiree.date,
            prixNormal: enCentimes(soiree.tarifs.normal),
            prixReduit: enCentimes(soiree.tarifs.reduit),
            nbNormal: 0,
            nbReduit: 0
        };

        panier.push(ligne);
    }

    ligne.nbNormal += nbN;
    ligne.nbReduit += nbR;

    ecrirePanier(panier);
    return true;
}

export function retirerSoiree(soireeId) {
    const panier = lirePanier();
    const nouveauPanier = [];

    for (let i = 0; i < panier.length; i++) {
        if (String(panier[i].soireeId) !== String(soireeId)) {
            nouveauPanier.push(panier[i]);
        }
    }

    ecrirePanier(nouveauPanier);
}

// -------------------- Calcul du panier

export function panierPourAffichage() {
    const panier = lirePanier();
    const lignes = [];
    let totalCentimes = 0;

    for (let i = 0; i < panier.length; i++) {
        const ligne = panier[i];

        const sousTotal =
            ligne.nbNormal * ligne.prixNormal
            + ligne.nbReduit * ligne.prixReduit;

        totalCentimes += sousTotal;

        lignes.push({
            soireeId: ligne.soireeId,
            nom: ligne.nom,
            date: ligne.date,
            nbNormal: ligne.nbNormal,
            nbReduit: ligne.nbReduit,
            prixNormal: formatPrix(ligne.prixNormal),
            prixReduit: formatPrix(ligne.prixReduit),
            sousTotal: formatPrix(sousTotal)
        });
    }

    return {
        lignes: lignes,
        vide: lignes.length === 0,
        total: formatPrix(totalCentimes)
    };
}

export function nombreBillets() {
    const panier = lirePanier();
    let total = 0;

    for (let i = 0; i < panier.length; i++) {
        total += panier[i].nbNormal + panier[i].nbReduit;
    }

    return total;
}


// -------------------- Affichage du panier (uniquement dans panier.html)

const contenuPanier = document.getElementById('contenu-panier');

if (contenuPanier) {
    contenuPanier.addEventListener('click', (evenement) => {
        const boutonRetirer = evenement.target.closest('.btn-retirer');
        if (boutonRetirer) {
            retirerSoiree(boutonRetirer.dataset.retirer);
            afficherPanier();
        }

        if (evenement.target.closest('#btn-vider-panier')) {
            viderPanier();
            afficherPanier();
        }
    });

    afficherPanier();
}

function afficherPanier() {
    displayPanier(panierPourAffichage());
}


// -------------------- Validation du panier (uniquement dans panier.html)

const btnValider = document.getElementById('btn-valider');

if (btnValider) {
    btnValider.addEventListener('click', async (evenement) => {
        const message = document.getElementById('msg-validation');
        evenement.disabled = true;

        try {
            const resultat = await validerPanier(lirePanier());
            viderPanier();
            // window.location.href = `paiement.html?commande=${encodeURIComponent(resultat.commande.id)}`;
        } catch (erreur) {
            message.textContent = erreur.message;
            bouton.disabled = false;
        }
    });
}