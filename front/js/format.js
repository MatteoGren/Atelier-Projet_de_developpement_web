const formatDate = new Intl.DateTimeFormat('fr-FR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
});

const formatDateCourte = new Intl.DateTimeFormat('fr-FR', {
    weekday: 'short',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
});

const formatPrix = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' });

export function dateLisible(date) {
    return formatDate.format(new Date(`${date}T00:00`));
}

export function dateCourte(date) {
    return formatDateCourte.format(new Date(`${date}T00:00`));
}

export function prixEuros(prix) {
    return formatPrix.format(prix);
}

export function urlVideoIntegree(url) {
    if (!url) {
        return null;
    }
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
