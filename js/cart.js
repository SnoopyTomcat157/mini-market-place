document.addEventListener('DOMContentLoaded', () => {
    const addToCartBtn = document.getElementById('addToCartBtn');
    
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', async () => {
            const buttonText = addToCartBtn.querySelector('.button-text');
            const productId = addToCartBtn.dataset.productId;
            const quantityInput = document.getElementById('quantityInput');
            const quantity = quantityInput ? quantityInput.value : 1;

            if (addToCartBtn.classList.contains('is-added')) {
                return;
            }

            addToCartBtn.disabled = true;
            if (buttonText) buttonText.textContent = 'Aggiungo...';

            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('product_id', productId);
            formData.append('quantity', quantity);

            try {
                await apiCall('api/cart.php', formData);

                addToCartBtn.classList.add('is-added');
                if (buttonText) buttonText.textContent = 'Aggiunto al carrello!';

                if (window.updateCartIcon) {
                    window.updateCartIcon();
                }

                // Dopo 2 secondi, ripristina il pulsante
                setTimeout(() => {
                    addToCartBtn.classList.remove('is-added');
                    if (buttonText) buttonText.textContent = 'Aggiungi al Carrello';
                    addToCartBtn.disabled = false;
                }, 2000); 

            } catch (error) {
                console.error('Errore:', error);
                if (buttonText) buttonText.textContent = 'Riprova';
                addToCartBtn.disabled = false;
                showFeedback(error.message, 'error');
            }
        });
    }
});
