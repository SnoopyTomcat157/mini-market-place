document.addEventListener('DOMContentLoaded', () => {
    const addToCartBtn = document.getElementById('addToCartBtn');
    const feedBackDiv = document.getElementById('feedbackMessage');

    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', async (event) =>{
            const productId = addToCartBtn.dataset.productId;
            const quantityInput = document.getElementById('quantityInput');
            const quantity = quantityInput ? quantityInput.value : 1;

            feedBackDiv.textContent = 'Aggiungo al carrello...';
            feedBackDiv.className = 'feedback-message';
            addToCartBtn.disabled = true;

            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('product_id', productId);
            formData.append('quantity', quantity);

            try{
                const response = await fetch('api/cart.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await respone.json();

                if(!response.ok){
                    throw new Error(result.message || 'Si è verificato un errore durante l\'aggiunta al carrello.');
                }

                feedBackDiv.textContent = 'Prodotto aggiunto al carrello con successo!';
                feedBackDiv.className = 'feedback-message success';

                const cartCount = document.getElementById('cart-item-count');
                if(cartCount && result.item_count !== undefined) {
                    cartCount.textContent = result.item_count;
                    cartCount.style.display = 'inline';
                }
            } catch (error) {
                console.error('Errore:', error);
                feedBackDiv.textContent = 'Errore durante l\'aggiunta al carrello: ' + error.message;
                feedBackDiv.className = 'feedback-message error';
            } finally{
                setTimeout(() => {
                    addToCartBtn.disabled = false;
                }, 1500);
            }
        });
    }
});