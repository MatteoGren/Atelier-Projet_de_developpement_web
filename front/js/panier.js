import { displayPanier } from './ui.js'; 
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

function modifierQuantite(soireeId, type, nouvelleQuantite) {
    const quantite = Number(nouvelleQuantite);

    if (!Number.isInteger(quantite) || quantite < 0) {
        return false;
    }

    if (type !== 'nbNormal' && type !== 'nbReduit') {
        return false;
    }

    const panier = lirePanier();
    const ligne = panier.find(
        element => String(element.soireeId) === String(soireeId)
    );

    if (!ligne) { return false; }

    ligne[type] = quantite;

    if (ligne.nbNormal === 0 && ligne.nbReduit === 0) {
        retirerSoiree(soireeId);
    } else {
        ecrirePanier(panier);
    }

    return true;
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
        nbSoirees: lignes.length,
        nbBillets: nombreBillets(),
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

        // -------------------- Gestion de la quantité

        const btnPlus = evenement.target.closest('.plus');
        const btnMoins = evenement.target.closest('.moins');

        if (!btnPlus && !btnMoins) {
            return;
        }

        const divQuantite = evenement.target.closest('.quantite');
        if (!divQuantite) return;

        const input = divQuantite.querySelector('input');
        const soireeId = divQuantite.dataset.soireeId;
        const type = divQuantite.dataset.type;

        if (btnPlus) {
            const nouvelleQuantite = Number(input.value) + 1;

            if (modifierQuantite(soireeId, type, nouvelleQuantite)) {
                afficherPanier();
            }
        }

        if (btnMoins) {
            const nouvelleQuantite = Math.max(0, Number(input.value) - 1); // ne pas descendre en dessous de 0

            if (modifierQuantite(soireeId, type, nouvelleQuantite)) {
                afficherPanier();
            }
        }


    });
    
    contenuPanier.addEventListener('change', (evenement) => {
        const input = evenement.target;

        if (!input.matches('.quantite input')) { return; }

        const bloc = input.closest('.quantite');

        const soireeId = bloc.dataset.soireeId;
        const type = bloc.dataset.type;

        const nouvelleQuantite = Number(input.value);

        if (!Number.isInteger(nouvelleQuantite) || nouvelleQuantite < 0) {
            input.value = 0;
            return;
        }

        if (modifierQuantite(soireeId, type, nouvelleQuantite)) {
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