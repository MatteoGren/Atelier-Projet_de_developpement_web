import { connexion, inscription } from './api.js';

const alertBox = document.getElementById('alert-box');

function notify(message, type = 'error') {
    alertBox.textContent = message;
    alertBox.className = `alert ${type === 'error' ? 'alert-error' : 'alert-success'}`;
    alertBox.style.display = 'block';
}

function clearNotification() {
    alertBox.textContent = '';
    alertBox.style.display = 'none';
}

// fetch lève une TypeError quand le serveur ne répond pas ; sinon c'est le message de l'API.
function messageErreur(err) {
    return err instanceof TypeError ? 'Serveur API inaccessible sur le port 31001.' : err.message;
}

function switchTab(tab) {
    clearNotification();
    const formConnexion = document.getElementById('form-connexion');
    const formInscription = document.getElementById('form-inscription');
    const btnConnexion = document.getElementById('btn-connexion');
    const btnInscription = document.getElementById('btn-inscription');

    if (tab === 'connexion') {
        formConnexion.classList.remove('hidden');
        formInscription.classList.add('hidden');
        btnConnexion.classList.add('active');
        btnInscription.classList.remove('active');
    } else {
        formConnexion.classList.add('hidden');
        formInscription.classList.remove('hidden');
        btnConnexion.classList.remove('active');
        btnInscription.classList.add('active');
    }
}

document.getElementById('btn-connexion').addEventListener('click', () => switchTab('connexion'));
document.getElementById('btn-inscription').addEventListener('click', () => switchTab('inscription'));

document.getElementById('form-connexion').addEventListener('submit', async (e) => {
    e.preventDefault();
    clearNotification();

    const payload = {
        email: document.getElementById('c-email').value,
        motDePasse: document.getElementById('c-motDePasse').value
    };

    try {
        const data = await connexion(payload);

        if (data.user) {
            localStorage.setItem('user', JSON.stringify(data.user));
            if (data.user.id) {
                localStorage.setItem('idUtilisateur', data.user.id);
            }
        }
        if (data.token) {
            localStorage.setItem('token', data.token);
        }

        window.location.href = 'index.html';
    } catch (err) {
        notify(messageErreur(err), 'error');
    }
});

document.getElementById('form-inscription').addEventListener('submit', async (e) => {
    e.preventDefault();
    clearNotification();

    const payload = {
        nom: document.getElementById('r-nom').value,
        prenom: document.getElementById('r-prenom').value,
        email: document.getElementById('r-email').value,
        telephone: document.getElementById('r-telephone').value,
        motDePasse: document.getElementById('r-motDePasse').value
    };

    try {
        await inscription(payload);

        switchTab('connexion');
        notify('Compte créé avec succès ! Connectez-vous.', 'success');
    } catch (err) {
        notify(messageErreur(err), 'error');
    }
});
