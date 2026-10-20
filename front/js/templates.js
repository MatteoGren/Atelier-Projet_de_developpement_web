export const IMAGE_PAR_DEFAUT = 'img/placeholder.svg';

const formatDate = new Intl.DateTimeFormat('fr-FR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});

// Les données viennent de la base : on échappe tout ce qui est injecté dans le HTML.
function echapper(texte) {
    return String(texte ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

const formatPrix = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' });

function dateLisible(date) {
    return formatDate.format(new Date(`${date}T00:00`));
}

export function carteSpectacle(spectacle) {
    // Le lien HATEOAS est « /soirees/{id} » : on en garde l'identifiant pour la page détail.
    const idSoiree = spectacle.lienSoiree.split('/').pop();

    return `
        <article class="carte-spectacle">
            <img src="${echapper(spectacle.image ?? IMAGE_PAR_DEFAUT)}" alt="${echapper(spectacle.titre)}" loading="lazy">
            <div class="carte-spectacle__infos">
                <h3><a href="soiree.html?id=${encodeURIComponent(idSoiree)}">${echapper(spectacle.titre)}</a></h3>
                <p>
                    <time datetime="${echapper(spectacle.date)}T${echapper(spectacle.horaire)}">
                        ${echapper(dateLisible(spectacle.date))} à ${echapper(spectacle.horaire)}
                    </time>
                </p>
            </div>
        </article>`;
}

export function listeSpectacles(spectacles) {
    return spectacles.map(carteSpectacle).join('');
}

// Lien YouTube « watch?v=ID » ou « youtu.be/ID » → adresse intégrable ; null sinon.
function urlVideoIntegree(url) {
    try {
        const adresse = new URL(url);
        let idVideo = null;
        if (adresse.hostname.endsWith('youtube.com')) {
            idVideo = adresse.searchParams.get('v');
        } else if (adresse.hostname === 'youtu.be') {
            idVideo = adresse.pathname.slice(1);
        }
        return idVideo ? `https://www.youtube-nocookie.com/embed/${encodeURIComponent(idVideo)}` : null;
    } catch {
        return null;
    }
}

function blocVideo(spectacle) {
    if (!spectacle.video) {
        return '';
    }
    const integree = urlVideoIntegree(spectacle.video);
    const lecteur = integree
        ? `<iframe src="${echapper(integree)}" title="Vidéo : ${echapper(spectacle.titre)}" loading="lazy" allowfullscreen></iframe>`
        : '';

    return `
        <div class="spectacle__video">
            ${lecteur}
            <p><a href="${echapper(spectacle.video)}" target="_blank" rel="noopener">Voir la vidéo</a></p>
        </div>`;
}

export function detailSpectacle(spectacle) {
    return `
        <article class="spectacle">
            <h3>${echapper(spectacle.titre)}</h3>
            <dl class="spectacle__infos">
                <dt>Artistes</dt>
                <dd>${echapper(spectacle.artistes)}</dd>
                <dt>Style</dt>
                <dd>${echapper(spectacle.style)}</dd>
            </dl>
            <p>${echapper(spectacle.description ?? 'Pas de description.')}</p>
            ${blocVideo(spectacle)}
        </article>`;
}

export function detailSoiree(soiree) {
    return `
        <article class="soiree">
            <header class="soiree__entete">
                <h2>${echapper(soiree.nom)}</h2>
                <p class="soiree__thematique">${echapper(soiree.thematique)}</p>
            </header>

            <dl class="soiree__infos">
                <dt>Date</dt>
                <dd><time datetime="${echapper(soiree.date)}">${echapper(dateLisible(soiree.date))}</time></dd>
                <dt>Horaire</dt>
                <dd><time datetime="${echapper(soiree.horaire)}">${echapper(soiree.horaire)}</time></dd>
                <dt>Lieu</dt>
                <dd>${echapper(soiree.lieu)}</dd>
            </dl>

            <section class="soiree__tarifs" aria-labelledby="titre-tarifs">
                <h3 id="titre-tarifs">Tarifs et billets</h3>
                <ul class="achat">
                    <li>
                        <label for="nb-normal">Normal : ${echapper(formatPrix.format(soiree.tarifs.normal))}</label>
                        <input type="number" id="nb-normal" min="0" max="10" value="1">
                    </li>
                    <li>
                        <label for="nb-reduit">Réduit : ${echapper(formatPrix.format(soiree.tarifs.reduit))}
                            <small>(étudiants, demandeurs d'emploi, personnes en situation de handicap)</small>
                        </label>
                        <input type="number" id="nb-reduit" min="0" max="10" value="0">
                    </li>
                </ul>
                <button type="button" id="btn-ajouter">Ajouter au panier</button>
                <p id="msg-panier" role="status"></p>
            </section>

            <section class="soiree__spectacles" aria-labelledby="titre-programme">
                <h3 id="titre-programme">Spectacles de la soirée</h3>
                <div class="liste-detail-spectacles">
                    ${soiree.spectacles.map(detailSpectacle).join('')}
                </div>
            </section>
        </article>`;
}
