document.addEventListener('DOMContentLoaded', () => {
    const addProductForm = document.getElementById('addProductForm');
    const editProductForm = document.getElementById('editProductForm');

    // gestisce il form per aggiungere un prodotto
    if (addProductForm) {
        addProductForm.addEventListener('submit', async (event) => {
            await handleFormSubmit(event, addProductForm, 'create');
        });
    }

    // gestisce il form per modificare un prodotto
    if (editProductForm) {
        editProductForm.addEventListener('submit', async (event) => {
            await handleFormSubmit(event, editProductForm, 'update');
        });
    }

    async function handleFormSubmit(event, form, action) {
        event.preventDefault();

        const submitButton = form.querySelector('button[type="submit"]');
        const feedbackDiv = document.getElementById('feedbackMessage');

        submitButton.disabled = true;
        feedbackDiv.textContent = 'Salvataggio in corso...';
        feedbackDiv.className = 'feedback-message';

        const formData = new FormData(form);
        formData.append('action', action);

        try {
            const response = await fetch('api/products.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Si è verificato un errore.');
            }

            feedbackDiv.textContent = result.message;
            feedbackDiv.className = 'feedback-message success';

            if (action === 'create') {
                form.reset(); // svuota il form solo se stiamo aggiungendo un nuovo prodotto
            } else if (action === 'update') {
                // dopo aver modificato, reindirizza l'utente alla dashboard dopo un breve ritardo
                feedbackDiv.textContent += ' Verrai reindirizzato alla dashboard...';
                setTimeout(() => {
                    window.location.href = 'seller_dashboard.php';
                }, 2000); // 2 secondi di attesa
            }

        } catch (error) {
            console.error('Errore:', error);
            feedbackDiv.textContent = error.message;
            feedbackDiv.className = 'feedback-message error';
        } finally {
            if (action !== 'update') {
                submitButton.disabled = false;
            }
        }
    }
});