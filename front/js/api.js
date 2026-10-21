import { API_URL } from './config.js';

export async function getSpectacles() {
    return chargerListe(`${API_URL}/spectacles`);
}

// Filtre vide ('') = non utilisé : l'API renvoie alors tous les spectacles.
export async function getSpectaclesFiltres(date, style) {
    const parametres = new URLSearchParams({ date, style });
    return chargerListe(`${API_URL}/spectacles/filtre?${parametres}`);
}

async function chargerListe(url) {
    const reponse = await fetch(url);
    if (!reponse.ok) {
        throw new Error(`Erreur ${reponse.status} lors du chargement des spectacles`);
    }
    const donnees = await reponse.json();
    // On garde le lien HATEOAS vers la soirée de chaque spectacle.
    return donnees.spectacles.map((item) => ({ ...item.spectacle, lienSoiree: item.links.soiree.href }));
}

// Renvoie null si la soirée n'existe pas (404) ou si l'identifiant est invalide (400).
export async function getSoiree(id) {
    const reponse = await fetch(`${API_URL}/soirees/${encodeURIComponent(id)}`);
    if (reponse.status === 404 || reponse.status === 400) {
        return null;
    }
    if (!reponse.ok) {
        throw new Error(`Erreur ${reponse.status} lors du chargement de la soirée`);
    }
    const donnees = await reponse.json();
    return donnees.soiree;
}


export async function validerPanier(panier) {
    const reponse = await fetch(`${API_URL}/commandes`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            lignes: panier.map((ligne) => ({
                idSoiree: ligne.soireeId,
                nbNormal: ligne.nbNormal,
                nbReduit: ligne.nbReduit
            }))
        })
    });

    const donnees = await reponse.json();
    if (!reponse.ok) {
        throw new Error(donnees.message ?? `Erreur ${reponse.status}`);
    }
    return donnees;  
}

// Renvoie null si la commande n'existe pas (404) ou si l'identifiant est invalide (400).
export async function getCommande(id) {
    const reponse = await fetch(`${API_URL}/commandes/${encodeURIComponent(id)}`);
    if (reponse.status === 404 || reponse.status === 400) {
        return null;
    }
    if (!reponse.ok) {
        throw new Error(`Erreur ${reponse.status} lors du chargement de la commande`);
    }
    const donnees = await reponse.json();
    return donnees.commande;
}

export async function payerCommande(id, carte) {
    const reponse = await fetch(`${API_URL}/commandes/${encodeURIComponent(id)}/paiement`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            numeroCarte: carte.numeroCarte,
            dateExpiration: carte.dateExpiration,
            code: carte.code
        })
    });

    const donnees = await reponse.json();
    if (!reponse.ok) {
        throw new Error(donnees.message ?? `Erreur ${reponse.status}`);
    }
    return donnees.commande;
}

// Renvoie null si la commande n'existe pas (404) ou si l'identifiant est invalide (400).
// Une commande non payée (409) n'a pas encore de billets : on remonte le message de l'API.
export async function getBilletsCommande(id) {
    const reponse = await fetch(`${API_URL}/commandes/${encodeURIComponent(id)}/billets`);
    if (reponse.status === 404 || reponse.status === 400) {
        return null;
    }
    const donnees = await reponse.json();
    if (!reponse.ok) {
        throw new Error(donnees.message ?? `Erreur ${reponse.status}`);
    }
    return donnees.billets.map((item) => avecToken(item.billet, item.links));
}

// Renvoie null si le token ne correspond à aucun billet payé.
export async function getBillet(token) {
    const reponse = await fetch(`${API_URL}/billets/${encodeURIComponent(token)}`);
    if (reponse.status === 404) {
        return null;
    }
    if (!reponse.ok) {
        throw new Error(`Erreur ${reponse.status} lors du chargement du billet`);
    }
    const donnees = await reponse.json();
    return avecToken(donnees.billet, donnees.links);
}

// Le token n'est donné que dans le lien HATEOAS du billet.
function avecToken(billet, links) {
    return { ...billet, token: links.self.href.split('/').pop() };
}
