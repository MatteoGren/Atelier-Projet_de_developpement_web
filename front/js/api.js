import { API_URL } from './config.js';

export async function getSpectacles() {
    const reponse = await fetch(`${API_URL}/spectacles`);
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
