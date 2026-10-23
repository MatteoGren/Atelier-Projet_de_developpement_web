import { API_URL } from './config.js';

// Programme complet, sans pagination : sert aux listes de filtres.
export async function getSpectacles() {
    return chargerListe(`${API_URL}/spectacles`);
}

// Renvoie { spectacles, page, pages } pour une page de 6 spectacles.
export async function getSpectaclePage(page) {
    const reponse = await fetch(`${API_URL}/spectacles?page=${page}`);
    if (!reponse.ok) {
        throw new Error(`Erreur ${reponse.status} lors du chargement de la page ${page}`);
    }
    const donnees = await reponse.json();
    return { spectacles: donnees.spectacles.map(versSpectacle), page: donnees.page, pages: donnees.pages };
}

// Filtre vide ('') = non utilisé : l'API renvoie alors tous les spectacles.
export async function getSpectaclesFiltres(date, style) {
    const parametres = new URLSearchParams({ date, style });
    return chargerListe(`${API_URL}/spectacles?${parametres}`);
}

// Renvoie { spectacle, links } ; lève une erreur avec le message de l'API en cas d'échec.
export async function creerSpectacle(payload) {
    const reponse = await fetch(`${API_URL}/spectacles`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', ...enTeteConnexion() },
        body: JSON.stringify(payload)
    });
    const donnees = await reponse.json().catch(() => ({}));
    if (!reponse.ok) {
        throw new Error(donnees.error ?? donnees.message ?? `Erreur ${reponse.status}`);
    }
    return donnees;
}


export async function creerSoiree(payload) {
    const reponse = await fetch(`${API_URL}/soirees`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            ...enTeteConnexion() // Inclus si la création nécessite d'être connecté
        },
        body: JSON.stringify(payload)
    });

    const donnees = await reponse.json().catch(() => ({}));

    if (!reponse.ok) {
        throw new Error(donnees.message ?? donnees.error ?? `Erreur ${reponse.status}`);
    }

    return donnees;
}

// Renvoie { utilisateur, links } ; lève une erreur avec le message de l'API en cas d'échec.
export async function connexion(payload) {
    return envoyerFormulaireAuth(`${API_URL}/sessions`, payload, 'Erreur lors de la connexion.');
}

export async function inscription(payload) {
    return envoyerFormulaireAuth(`${API_URL}/utilisateurs`, payload, "Erreur lors de l'inscription.");
}

async function envoyerFormulaireAuth(url, payload, messageParDefaut) {
    const reponse = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    const donnees = await reponse.json().catch(() => ({}));
    if (!reponse.ok) {
        throw new Error(donnees.error || donnees.message || messageParDefaut);
    }
    return donnees;
}


async function chargerListe(url) {
    const reponse = await fetch(url);
    if (!reponse.ok) {
        throw new Error(`Erreur ${reponse.status} lors du chargement des spectacles`);
    }
    const donnees = await reponse.json();
    return donnees.spectacles.map(versSpectacle);
}

// On garde le lien HATEOAS vers la soirée de chaque spectacle.
function versSpectacle(item) {
    const lienSoiree = item.links.soiree?.href ?? null;
    const idSoiree = item.spectacle.soiree_id ?? null;
    return { ...item.spectacle, idSoiree, lienSoiree };
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
    const connexion = enTeteConnexion();

    const reponse = await fetch(`${API_URL}/commandes`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            ...connexion
        },
        body: JSON.stringify({
            lignes: panier.map((ligne) => ({
                idSoiree: String(ligne.soireeId),
                nbNormal: Number(ligne.nbNormal),
                nbReduit: Number(ligne.nbReduit)
            }))
        })
    });

    if (reponse.status === 401) {
        const erreur = new Error('Connexion requise');
        erreur.status = 401;
        throw erreur;
    }

    const donnees = await reponse.json();
    if (!reponse.ok) {
        const erreur = new Error(donnees.message ?? `Erreur ${reponse.status}`);
        erreur.status = reponse.status;
        throw erreur;
    }

    return donnees;
}

// En-tête de connexion avec le jeton JWT reçu à la connexion ; erreur 401 si personne n'est connecté.
function enTeteConnexion() {
    const jeton = localStorage.getItem('token');
    if (!jeton) {
        throw erreurConnexion();
    }
    return { 'Authorization': `Bearer ${jeton}` };
}

function erreurConnexion() {
    const erreur = new Error('Connexion requise');
    erreur.status = 401;
    return erreur;
}

// Renvoie null si la commande n'existe pas (404) ou si l'identifiant est invalide (400).
export async function getCommande(id) {
    const reponse = await fetch(`${API_URL}/commandes/${encodeURIComponent(id)}`, {
        headers: enTeteConnexion()
    });
    if (reponse.status === 401) {
        throw erreurConnexion();
    }
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
        headers: { 'Content-Type': 'application/json', ...enTeteConnexion() },
        body: JSON.stringify({
            numeroCarte: carte.numeroCarte,
            dateExpiration: carte.dateExpiration,
            code: carte.code
        })
    });
    if (reponse.status === 401) {
        throw erreurConnexion();
    }

    const donnees = await reponse.json();
    if (!reponse.ok) {
        throw new Error(donnees.message ?? `Erreur ${reponse.status}`);
    }
    return donnees.commande;
}

// Renvoie null si la commande n'existe pas (404) ou si l'identifiant est invalide (400).
// Une commande non payée (409) n'a pas encore de billets : on remonte le message de l'API.
export async function getBilletsCommande(id) {
    const reponse = await fetch(`${API_URL}/commandes/${encodeURIComponent(id)}/billets`, {
        headers: enTeteConnexion()
    });
    if (reponse.status === 401) {
        throw erreurConnexion();
    }
    if (reponse.status === 404 || reponse.status === 400) {
        return null;
    }
    const donnees = await reponse.json();
    if (!reponse.ok) {
        throw new Error(donnees.message ?? `Erreur ${reponse.status}`);
    }
    return donnees.billets.map((item) => avecToken(item.billet, item.links));
}

// Billets payés de l'utilisateur connecté, toutes commandes confondues.
export async function getMesBillets() {
    const reponse = await fetch(`${API_URL}/billets`, {
        headers: enTeteConnexion()
    });
    if (reponse.status === 401) {
        throw erreurConnexion();
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

export async function getStatistiquesVentes() {
    const reponse = await fetch(`${API_URL}/soirees`, { headers: enTeteConnexion() });

    if (!reponse.ok) {
        throw new Error(`Erreur HTTP ${reponse.status}`);
    }

    return reponse.json();
}
