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

// Ajoutées après l'option « Toutes les … » déjà écrite dans la page.
function ajouterOptions(selecteur, valeurs, libelle) {
    const templateOptions = Handlebars.compile(document.querySelector('#tpl-options').innerHTML);

    const options = valeurs.map((valeur) => ({ valeur, libelle: libelle(valeur) }));

    document.querySelector(selecteur).insertAdjacentHTML('beforeend', templateOptions({ options }));
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

export function displayPaiement(commande) {
    const templatePaiement = Handlebars.compile(document.querySelector('#tpl-paiement').innerHTML);

    document.querySelector('#contenu-paiement').innerHTML = templatePaiement({
        ...commande,
        montantFormate: prixEuros(commande.montantTotal)
    });
}

export function displayConfirmation(commande) {
    const templateConfirmation = Handlebars.compile(document.querySelector('#tpl-confirmation').innerHTML);

    document.querySelector('#contenu-paiement').innerHTML = templateConfirmation({
        ...commande,
        montantFormate: prixEuros(commande.montantTotal)
    });
}

const TEMPLATE_NAV = `
    <a href="index.html">Programme</a>
    <a href="billets.html">Mes billets</a>
    <a href="panier.html">Panier</a>
    {{#if backoffice}}
    <a href="Backoffice.html">Backoffice</a>
    <a href="tableaudebord.html">Tableau de Bord</a>
    {{/if}}

    <a href="auth.html" id="lien-connexion">Connexion</a>
    <span id="zone-utilisateur" style="display: none;">
        <a href="#" id="btn-deconnexion"> Déconnexion</a>
        (<span id="nom-utilisateur"></span>)
    </span>`;

export function displayNav(nav, backoffice) {
    const templateNav = Handlebars.compile(TEMPLATE_NAV);

    nav.innerHTML = templateNav({ backoffice });
}

// erreur = true : le chargement a échoué ; liste vide : le template affiche « Aucun spectacle libre ».
export function displaySpectaclesLibres(spectacles, erreur = false) {
    const templateSpectaclesLibres = Handlebars.compile(document.querySelector('#tpl-spectacles-libres').innerHTML);

    const donnees = spectacles.map((spectacle) => {
        // Concaténation avec des points médians comme sur la maquette
        const details = [spectacle.artistes || spectacle.artiste || '', spectacle.style || ''].filter(Boolean).join(' · ');
        const titre = spectacle.titre || 'Sans titre';
        return {
            id: spectacle.id,
            texteComplet: details ? `${titre} · ${details}` : titre
        };
    });

    document.querySelector('#liste-spectacles-selection').innerHTML = templateSpectaclesLibres({ spectacles: donnees, erreur });
}

export function displayBillets(billets) {
    const templateBillets = Handlebars.compile(document.querySelector('#tpl-billets').innerHTML);

    const donnees = billets.map((billet) => ({
        ...billet,
        dateFormatee: dateLisible(billet.date),
        prixFormate: prixEuros(billet.prix),
        categorieFormatee: billet.categorie === 'reduit' ? 'Tarif réduit' : 'Tarif normal',
        lienPersonnel: `billets.html?token=${encodeURIComponent(billet.token)}`
    }));

    document.querySelector('#liste-billets').innerHTML = templateBillets({ billets: donnees });
}


export function displayTableauBord(statistiques) {
    const formatNombre = new Intl.NumberFormat('fr-FR');
    const formatEuro = new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    });

    document.querySelector('#total-places').textContent =
        formatNombre.format(statistiques.totalPlaces);

    document.querySelector('#capacite-totale').textContent =
        `sur ${formatNombre.format(statistiques.capaciteTotale)} places`;

    document.querySelector('#recette-totale').textContent =
        formatEuro.format(statistiques.recetteTotale);

    const template = Handlebars.compile(
        document.querySelector('#tpl-carte-soiree').innerHTML
    );

    const soirees = statistiques.soirees.map((soiree) => {
        const taux = soiree.capacite > 0
            ? (soiree.placesVendues / soiree.capacite) * 100
            : 0;

        return {
            ...soiree,
            taux,
            tauxFormate: taux >= 100
                ? 'Complet'
                : `${taux.toLocaleString('fr-FR', {
                    maximumFractionDigits: 1
                })} %`,
            placesFormatees: formatNombre.format(soiree.placesVendues),
            capaciteFormatee: formatNombre.format(soiree.capacite),
            recetteFormatee: formatEuro.format(soiree.recette)
        };
    });

    document.querySelector('#grille-soirees').innerHTML =
        template({ soirees });
}

