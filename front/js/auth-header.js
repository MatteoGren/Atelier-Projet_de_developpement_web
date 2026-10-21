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
            window.location.reload();
        });
    }
});