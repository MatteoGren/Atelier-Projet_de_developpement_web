import { dateCourte, dateLisible, prixEuros, urlVideoIntegree } from './format.js';

export const IMAGE_PAR_DEFAUT = 'img/placeholder.svg';

export function displaySpectacles(spectacles) {
    const templateSpectacles = Handlebars.compile(document.querySelector('#tpl-spectacle').innerHTML);

    const donnees = spectacles.map((spectacle) => {
        const idSoiree = spectacle.lienSoiree.split('/').pop();
        return {
            ...spectacle,
            image: spectacle.image || IMAGE_PAR_DEFAUT,
            lienDetail: `soiree.html?id=${encodeURIComponent(idSoiree)}`,
            dateFormatee: dateLisible(spectacle.date)
        };
    });

    document.querySelector('#liste-spectacles').innerHTML = templateSpectacles({ spectacles: donnees });
}

// Options des listes déroulantes, tirées du programme complet (dates et styles sans doublon).
export function displayFiltres(spectacles) {
    const dates = [...new Set(spectacles.map((spectacle) => spectacle.date))].sort();
    const styles = [...new Set(spectacles.map((spectacle) => spectacle.style))].sort((a, b) => a.localeCompare(b, 'fr'));

    ajouterOptions('#filtre-date', dates, dateCourte);
    ajouterOptions('#filtre-style', styles, majuscule);
}

function ajouterOptions(selecteur, valeurs, libelle) {
    const liste = document.querySelector(selecteur);
    valeurs.forEach((valeur) => liste.add(new Option(libelle(valeur), valeur)));
}

export function majuscule(texte) {
    return texte.charAt(0).toUpperCase() + texte.slice(1);
}

export function displaySoiree(soiree) {
    const templateSoiree = Handlebars.compile(
        document.querySelector('#tpl-soiree').innerHTML
    );

    const donnees = {
        ...soiree,
        dateFormatee: dateLisible(soiree.date),
        prixNormalFormate: prixEuros(soiree.tarifs.normal),
        prixReduitFormate: prixEuros(soiree.tarifs.reduit),
        spectacles: soiree.spectacles.map((spectacle) => ({
            ...spectacle,
            description: spectacle.description || 'Pas de description.',
            urlVideoIntegree: urlVideoIntegree(spectacle.video)
        }))
    };

    document.querySelector('#detail-soiree').innerHTML = templateSoiree(donnees);
}

export function displayPanier(panier) {
    const templatePanier = Handlebars.compile(document.querySelector('#tpl-panier').innerHTML);

    document.querySelector('#contenu-panier').innerHTML = templatePanier(panier);
}
