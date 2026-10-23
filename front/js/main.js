import { getSpectacles, getSpectaclePage, getSpectaclesFiltres } from './api.js';
import { displayFiltres, displayPagination, displaySpectacles, IMAGE_PAR_DEFAUT } from './ui.js';

const conteneur = document.getElementById('liste-spectacles');
const message = document.getElementById('message');
const formulaire = document.getElementById('filtres');
const pagination = document.getElementById('pagination');
let messageProgramme = '';

conteneur.addEventListener('error', (evenement) => {
    const image = evenement.target;
    if (image.tagName !== 'IMG') return;
    // Copie réduite absente : on retente avec l'image d'origine avant le placeholder.
    if (image.hasAttribute('srcset')) {
        image.removeAttribute('srcset');
    } else if (!image.src.endsWith(IMAGE_PAR_DEFAUT)) {
        image.src = IMAGE_PAR_DEFAUT;
    }
}, true);

// Liste complète pour les filtres et le total, puis affichage de la page 1.
async function chargerSpectacles() {
    try {
        const spectacles = await getSpectacles();
        if (spectacles.length === 0) {
            message.textContent = 'Aucun spectacle programmé pour le moment.';
            return;
        }

        displayFiltres(spectacles);
        messageProgramme = `${spectacles.length} spectacles prévus`;
        await afficherPage(1);
    } catch (erreur) {
        message.textContent = 'Impossible de charger les spectacles. Réessayez plus tard.';
        console.error(erreur);
    }
}

async function afficherPage(numero) {
    try {
        const { spectacles, page, pages } = await getSpectaclePage(numero);
        displaySpectacles(spectacles);
        displayPagination(page, pages);
        message.textContent = pages > 1 ? `${messageProgramme} · page ${page} sur ${pages}` : messageProgramme;
    } catch (erreur) {
        message.textContent = 'Impossible de charger cette page. Réessayez plus tard.';
        console.error(erreur);
    }
}

pagination.addEventListener('click', (evenement) => {
    const bouton = evenement.target.closest('button[data-page]');
    if (bouton) {
        afficherPage(Number(bouton.dataset.page));
    }
});

async function filtrerSpectacles() {
    const date = formulaire.elements.date;
    const style = formulaire.elements.style;
    // Libellés affichés dans les listes, réutilisés pour résumer l'état du filtre.
    const etat = `État filtré : ${date.selectedOptions[0].text} · ${style.selectedOptions[0].text}`;

    message.textContent = 'Chargement des spectacles...';
    try {
        const spectacles = await getSpectaclesFiltres(date.value, style.value);
        displaySpectacles(spectacles);
        displayPagination(1, 1);
        message.textContent = spectacles.length === 0
            ? `${etat} · aucun spectacle ne correspond`
            : `${etat} · ${spectacles.length} résultat${spectacles.length > 1 ? 's' : ''}`;
    } catch (erreur) {
        message.textContent = 'Impossible de filtrer les spectacles. Réessayez plus tard.';
        console.error(erreur);
    }
}

formulaire.addEventListener('submit', (evenement) => {
    evenement.preventDefault();
    filtrerSpectacles();
});

// Le reset retire le filtre : retour à la page 1 du programme.
formulaire.addEventListener('reset', () => {
    afficherPage(1);
});

chargerSpectacles();
