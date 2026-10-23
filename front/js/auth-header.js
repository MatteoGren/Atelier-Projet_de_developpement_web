// Nav commune à toutes les pages : écrite ici une seule fois dans <nav id="nav">.
const nav = document.getElementById('nav');
const utilisateurAutorise = (() => {
    try {
        const utilisateur = JSON.parse(localStorage.getItem('user') || 'null');
        return ['organisateur', 'administrateur'].includes(utilisateur?.role);
    } catch {
        return false;
    }
})();

if (nav) {
    nav.innerHTML = `
        <a href="index.html">Programme</a>
        <a href="billets.html">Mes billets</a>
        <a href="panier.html">Panier</a>
        ${utilisateurAutorise ? '<a href="Backoffice.html">Backoffice</a>' : ''}
        ${utilisateurAutorise ? '<a href="tableaudebord.html">Tableau de Bord</a>' : ''}

        <a href="auth.html" id="lien-connexion">Connexion</a>
        <span id="zone-utilisateur" style="display: none;">
            <a href="#" id="btn-deconnexion"> Déconnexion</a>
            (<span id="nom-utilisateur"></span>)
        </span>`;
}

document.addEventListener('DOMContentLoaded', () => {
    const lienConnexion = document.getElementById('lien-connexion');
    const zoneUtilisateur = document.getElementById('zone-utilisateur');
    const nomUtilisateur = document.getElementById('nom-utilisateur');
    const btnDeconnexion = document.getElementById('btn-deconnexion');

    const userRaw = localStorage.getItem('user');

    if (userRaw) {
        try {
            const user = JSON.parse(userRaw);

            // Masque le lien "Connexion" et affiche la zone utilisateur
            if (lienConnexion) lienConnexion.style.display = 'none';
            if (zoneUtilisateur) zoneUtilisateur.style.display = 'inline';

            
            const displayName = (user.nom && user.prenom)
                ? `${user.prenom} ${user.nom}`
                : (user.email || '');

            if (nomUtilisateur) {
                nomUtilisateur.textContent = displayName;
            }
        } catch (e) {
            localStorage.removeItem('user');
        }
    }

    // Gestion du clic sur "Déconnexion"
    if (btnDeconnexion) {
        btnDeconnexion.addEventListener('click', (evenement) => {
            evenement.preventDefault();
            localStorage.removeItem('user');
            localStorage.removeItem('token');
            localStorage.removeItem('idUtilisateur');
            window.location.reload();
        });
    }
});


// ------------------- Menu Mobile
const entete = document.querySelector('.entete');
const btnMenu = document.getElementById('btn-menu');

if (entete && btnMenu) {
    btnMenu.addEventListener('click', () => {
        const menuOuvert = entete.classList.toggle('menu-ouvert');

        btnMenu.textContent = menuOuvert ? 'X' : 'Menu';
    });
}