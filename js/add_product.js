document.addEventListener('DOMContentLoaded', () =>{
    const addProductForm = document.getElementById('addProductForm');

    if (addProductForm) {
        addProductForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const submitButton = addProductForm.querySelector('button[type="submit"]');
            const feedbackDiv = document.getElementById('feedbackMessage');

            submitButton.disabled = true;
            feedbackDiv.textContent = 'Aggiunta del prodotto in corso...';
            feedbackDiv.className = 'feedback-message';

            const formData = new FormData(addProductForm);
            formData.append('action', 'create');

            try {
                const response = await fetch('api/product.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if(!result.ok){
                    throw new Error(result.message || 'Si è verificato un errore durante l\'aggiunta del prodotto.');
                }

                //se arrivo qui successo
                feedbackDiv.textContent = result.message;
                feedbackDiv.className = 'feedback-message success';
                addProductForm.reset();
            } catch (error){
                console.error("Errore:", error);
                feedbackDiv.textContent = error.message;
                feedbackDiv.className = 'feedback-message error';
            } finally {
                submitButton.disabled = false;
            }
        });
    }
}); 