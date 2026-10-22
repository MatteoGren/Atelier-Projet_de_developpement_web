import { creerSpectacle, creerSoiree, getSpectacles } from './api.js';

function verifierAccesBackoffice() {
    let utilisateur = null;

    try {
        utilisateur = JSON.parse(localStorage.getItem('user') || 'null');
    } catch {
        utilisateur = null;
    }

    const rolesAutorises = ['organisateur', 'administrateur'];
    if (!utilisateur || !rolesAutorises.includes(utilisateur.role)) {
        window.location.replace('auth.html');
        return false;
    }

    return true;
}

window.toggleForm = function (type) {
    const soireeBox = document.getElementById('form-soiree-box');
    const spectacleBox = document.getElementById('form-spectacle-box');
    const btnSoiree = document.getElementById('btn-toggle-soiree');
    const btnSpectacle = document.getElementById('btn-toggle-spectacle');

    if (type === 'soiree') {
        const isOpen = soireeBox.classList.toggle('visible');
        btnSoiree?.classList.toggle('active', isOpen);
    } else if (type === 'spectacle') {
        const isOpen = spectacleBox.classList.toggle('visible');
        btnSpectacle?.classList.toggle('active', isOpen);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    if (!verifierAccesBackoffice()) return;



    // Formulaire Spectacle
    const formSpectacle = document.getElementById('form-spectacle');

    if (formSpectacle) {
        formSpectacle.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(formSpectacle);
            const payload = {
                titre: formData.get('titre')?.trim(),
                artistes: formData.get('artistes')?.trim(),
                description: formData.get('description')?.trim() || null,
                style: formData.get('style')?.trim(),
                horaire: formData.get('horaire'),
                images: formData.get('images')?.trim() || '',
                chemin_video: formData.get('chemin_video')?.trim() || null,
            };

            const submitBtn = formSpectacle.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            try {
                const res = await creerSpectacle(payload);
                alert(`Spectacle créé avec succès ! (ID : ${res.id || 'ok'})`);
                formSpectacle.reset();
                window.toggleForm('spectacle');
            } catch (err) {
                console.error(err);
                alert(err.message || 'Erreur lors de la création du spectacle.');
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }

    // Formulaire Soirée
    const formSoiree = document.getElementById('form-soiree');

    if (formSoiree) {
        formSoiree.addEventListener('submit', async (e) => {
            e.preventDefault();

            //Récupération des IDs des spectacles sélectionnés
            const checkboxesCochees = document.querySelectorAll('input[name="spectacles[]"]:checked');
            const spectaclesSelectionnes = Array.from(checkboxesCochees).map(cb => cb.value);

            // Vérification côté client
            if (spectaclesSelectionnes.length < 2 || spectaclesSelectionnes.length > 3) {
                alert("Veuillez sélectionner 2 ou 3 spectacles.");
                return;
            }

            const dateVal = document.getElementById('date-soiree').value;
            const heureVal = document.getElementById('horaire-debut').value || '20:00';

            if (!dateVal) {
                alert('Veuillez renseigner une date.');
                return;
            }

            //Préparation des données complètes à envoyer au serveur
            const donneesSoiree = {
                nom: document.getElementById('nom-soiree').value.trim(),
                thematique: document.getElementById('thematique').value,
                date: `${dateVal} ${heureVal}:00`,
                lieu: document.getElementById('lieu-soiree').value.trim(),
                capacite: parseInt(document.getElementById('jauge-soiree').value, 10),
                spectacles: spectaclesSelectionnes
            };

            const submitBtn = formSoiree.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            try {
                const resultat = await creerSoiree(donneesSoiree);
                alert(`Soirée enregistrée avec succès ! (ID : ${resultat.id || 'OK'})`);

                formSoiree.reset();

                // Réinitialiser le compteur (0/3)
                const compteur = document.getElementById('compteur-spectacles');
                if (compteur) compteur.textContent = '(0/3)';

                // Recharger les spectacles disponibles
                await chargerSpectaclesDisponibles();

                window.toggleForm('soiree');
            } catch (err) {
                console.error(err);
                alert(`Erreur : ${err.message}`);
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }
});

async function chargerSpectaclesDisponibles() {
    const conteneur = document.getElementById('liste-spectacles-selection');
    if (!conteneur) return;

    try {
        const spectacles = (await getSpectacles()).filter((spectacle) => !spectacle.idSoiree);

        if (!Array.isArray(spectacles) || spectacles.length === 0) {
            conteneur.innerHTML = '<p>Aucun spectacle libre. Créez d’abord un spectacle, puis associez-le à une soirée.</p>';
            return;
        }

        conteneur.innerHTML = '';

        spectacles.forEach(spec => {
            const label = document.createElement('label');
            label.className = 'item-spectacle';

            const titre = spec.titre || 'Sans titre';
            const artistes = spec.artistes || spec.artiste || '';
            const style = spec.style || '';
            const id = spec.id;

            // Concaténation avec des points médians comme sur la maquette
            const details = [artistes, style].filter(Boolean).join(' · ');
            const texteComplet = details ? `${titre} · ${details}` : titre;

            label.innerHTML = `
                <input type="checkbox" name="spectacles[]" value="${id}">
                <span>${texteComplet}</span>
            `;

            conteneur.appendChild(label);
        });

        // Initialise la gestion du compteur (x/3) et la limite de 3 sélections
        initialiserGestionSelection();

    } catch (erreur) {
        console.error('Erreur chargement spectacles :', erreur);
        conteneur.innerHTML = '<p>Impossible de charger les spectacles.</p>';
    }
}

// Fonction de gestion du compteur (0/3) et de la limite
function initialiserGestionSelection() {
    const checkboxes = document.querySelectorAll('input[name="spectacles[]"]');
    const compteur = document.getElementById('compteur-spectacles');

    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            const coches = document.querySelectorAll('input[name="spectacles[]"]:checked');

            // Bloquer si l'utilisateur essaie d'en sélectionner plus de 3
            if (coches.length > 3) {
                cb.checked = false;
                alert("Vous ne pouvez pas associer plus de 3 spectacles à une soirée.");
                return;
            }

            if (compteur) {
                compteur.textContent = `(${coches.length}/3)`;
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', chargerSpectaclesDisponibles);