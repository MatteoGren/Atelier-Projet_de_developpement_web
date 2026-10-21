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