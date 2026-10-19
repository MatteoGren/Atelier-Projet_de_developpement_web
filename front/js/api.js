import { API_URL } from './config.js';

export async function getSpectacles() {
    const reponse = await fetch(`${API_URL}/spectacles`);
    if (!reponse.ok) {
        throw new Error(`Erreur ${reponse.status} lors du chargement des spectacles`);
    }
    const donnees = await reponse.json();
    return donnees.spectacles.map((item) => item.spectacle);
}
