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
        return JSON.parse(donnees);
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

// -------------------- Ajouter des billets

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

// -------------------- Retirer une soirée

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

// -------------------- Calculer le panier

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

// -------------------- Nombre de billets

export function nombreBillets() {
    const panier = lirePanier();
    let total = 0;

    for (let i = 0; i < panier.length; i++) {
        total += panier[i].nbNormal + panier[i].nbReduit;
    }

    return total;
}

// -------------------- Affichage de la page panier

const template = document.getElementById('tpl-ligne-panier');
const contenuPanier = document.getElementById('contenu-panier');

function afficherPanier() {
    const panier = panierPourAffichage();

    contenuPanier.innerHTML = '';

    if (panier.vide) {
        contenuPanier.textContent = 'Votre panier est vide.';
        return;
    }

    for (let i = 0; i < panier.lignes.length; i++) {
        const ligne = panier.lignes[i];
        const copie = template.content.cloneNode(true);

        copie.querySelector('.nom').textContent = ligne.nom;
        copie.querySelector('.date').textContent = ligne.date;

        copie.querySelector('.billets').textContent =
            ligne.nbNormal + ' billets normaux, '
            + ligne.nbReduit + ' billets réduits';

        copie.querySelector('.sous-total').textContent =
            'Sous-total : ' + ligne.sousTotal;

        copie.querySelector('.btn-retirer').dataset.retirer =
            ligne.soireeId;

        contenuPanier.appendChild(copie);
    }

    const total = document.createElement('p');
    total.textContent = 'Total : ' + panier.total;
    contenuPanier.appendChild(total);
}

// Afficher le panier uniquement sur la page panier.html
if (contenuPanier && template) {
    afficherPanier();

    contenuPanier.addEventListener('click', function(event) {
        const bouton = event.target.closest('.btn-retirer');

        if (bouton) {
            retirerSoiree(bouton.dataset.retirer);
            afficherPanier();
        }
    });
}