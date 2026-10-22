import { creerSpectacle } from './api.js';

window.toggleForm = function (type) {
    const soireeBox = document.getElementById('form-soiree-box');
    const spectacleBox = document.getElementById('form-spectacle-box');
    const btnSoiree = document.getElementById('btn-toggle-soiree');
    const btnSpectacle = document.getElementById('btn-toggle-spectacle');

    if (type === 'soiree') {
        const isOpen = soireeBox.classList.toggle('visible');
        btnSoiree.classList.toggle('active', isOpen);
    } else if (type === 'spectacle') {
        const isOpen = spectacleBox.classList.toggle('visible');
        btnSpectacle.classList.toggle('active', isOpen);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    const formSpectacle = document.getElementById('form-spectacle');

    if (formSpectacle) {
        formSpectacle.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(formSpectacle);

            // On envoie les valeurs brutes attendues par CreerSpectacleAction
            const payload = {
                titre: formData.get('titre')?.trim(),
                artistes: formData.get('artistes')?.trim(),
                description: formData.get('description')?.trim() || null,
                style: formData.get('style')?.trim(),
                horaire: formData.get('horaire'),
                
                images: formData.get('images')?.trim() || '',
                chemin_video: formData.get('chemin_video')?.trim() || null,
                soiree_id: formData.get('soiree_id')?.trim() || null
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
});