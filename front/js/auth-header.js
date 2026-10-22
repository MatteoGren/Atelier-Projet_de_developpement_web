// Nav commune à toutes les pages : écrite ici une seule fois dans <nav id="nav">.
const nav = document.getElementById('nav');
if (nav) {
    nav.innerHTML = `
        <a href="index.html">Programme</a>
        <a href="panier.html">Panier</a>
        <a href="billets.html">Billets</a>
        <a href="Backoffice.html">Backoffice</a>

        <a href="auth.html" id="lien-connexion">> Connexion</a>
        <span id="zone-utilisateur" style="display: none;">
            <span id="nom-utilisateur" style="color: #c85a17;"></span>
            <a href="#" id="btn-deconnexion" style="text-decoration: underline; color: #000; margin-left: 8px;">> Déconnexion</a>
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

            // Formatage : "nom prenom" en minuscules
            const displayName = (user.nom && user.prenom)
                ? `${user.nom} ${user.prenom}`.toLowerCase()
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