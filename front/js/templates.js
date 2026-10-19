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

export function carteSpectacle(spectacle) {
    const dateLisible = formatDate.format(new Date(`${spectacle.date}T00:00`));

    return `
        <article class="carte-spectacle">
            <img src="${echapper(spectacle.image ?? IMAGE_PAR_DEFAUT)}" alt="${echapper(spectacle.titre)}" loading="lazy">
            <div class="carte-spectacle__infos">
                <h3>${echapper(spectacle.titre)}</h3>
                <p>
                    <time datetime="${echapper(spectacle.date)}T${echapper(spectacle.horaire)}">
                        ${echapper(dateLisible)} à ${echapper(spectacle.horaire)}
                    </time>
                </p>
            </div>
        </article>`;
}

export function listeSpectacles(spectacles) {
    return spectacles.map(carteSpectacle).join('');
}
