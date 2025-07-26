document.addEventListener('DOMContentLoaded', () => {
    const cartContainer = document.querySelector('.cart-container');

    if (cartContainer) {
        cartContainer.addEventListener('click', async (event) => {
            if (event.target && event.target.classList.contains('remove-from-cart')){
                const button = event.target;
                const productId = button.dataset.itemId;

                if(confirm('Sei sicuro di voler rimuovere questo oggetto dal carrello?')){
                    await updateCart('remove', productId, 0);
                }
            }
        });

        cartContainer.addEventListener('change', async (event) =>{
            if (event.target && event.target.classList.contains('update-quantity')){
                const input = event.target;
                const productId = input.dataset.productId;
                const quantity = parseInt(input.value, 10);

                if (quantity > 0) {
                    await updateCart('update', productId, quantity);
                } else {
                    //se quantità è 0 lo tratto comne una rimozione
                    await updateCart('remove', productId, 0);
                }
            }
        });
    }


    async function updateCart(action, productId, quantity) {
        const formData = new FormData();
        formData.append('action', action);
        formData.append('product_id', productId);
        formData.append('quantity', quantity);

        try{
            const response = await fetch('/cart/update', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if(!response.ok) {
                throw new Error(result.message || 'Errore durante del server');
            }

            updateCartView(result.cartState);
        } catch (error) {
            console.error('Errore durante l\'aggiornamento del carrello:', error);
            alert('Si è verificato un errore durante l\'aggiornamento del carrello. Riprova più tardi.' + error.message);
        }

    }

    function updateCartView(cartState){
        const cartTableBody = document.querySelector('.cart-table tbody');
        const totalElement = document.querySelector('.cart-summary strong');
        const headerCartCount = document.getElementById('cart-item-count');
        const cartContent = document.querySelector('.cart-content');
        const cartEmptyMessage = document.querySelector('.cart-empty');

        if(headerCartCount){
            headerCartCount.textContent = cartState.itemCount > 0 ? cartState.itemCount : '';
        }

        if(cartState.itemCount === 0) {
            if(cartContent) cartContent.style.display = 'none';
            if(cartEmptyMessage) cartEmptyMessage.style.display = 'block';
            return;
        }

        if(cartContent) cartContent.style.display = 'grid';
        if(cartEmptyMessage) cartEmptyMessage.style.display = 'none';
    

    if(totalElement) {
        totalElement.textContent = `${parseFloat(cartState.totalPrice).toFixed(2).replace('.', ',')} €`;
    }


    if(cartTableBody) {
        cartTableBody.innerHTML = '';

        cartState.items.forEach (item => {
            const row = document.createElement('tr');

            const cellaImmagine = document.createElement('td');
            cellaImmagine.dataset.labale = 'Immagine';

            const img = document.createElement('img');
            img.src = `uploads/products/${item.image ? item.image : 'default_image.png'}`;
            img.alt = item.name;
            img.className = 'cart-product-img';
            cellaImmagine.appendChild(img);
            row.appendChild(cellaImmagine);


            const cellaNome = document.createElement('td');
            nomeCella.dataset.label = 'Nome';
            nomeCella.textContent = item.nome;
            row.appendChild(cellaNome);

            const cellaPrezzo = document.createElement('td');
            cellaPrezzo.dataset.label = 'Prezzo';
            cellaPrezzo.textContent = `${parseFloat(item.prezzo).toFixed(2).replace('.', ',')} €`;
            row.appendChild(cellaPrezzo);

            const cellaQuantita = document.createElement('td');
            cellaQuantita.dataset.label = 'Quantità';
            const inputQuantita = document.createElement('input');
            inputQuantita.type = 'number';
            inputQuantita.value = item.quantity;
            inputQuantita.min = '1';
            inputQuantita.className = 'update-quantity';
            inputQuantita.dataset.productId = item.id;
            cellaQuantita.appendChild(inputQuantita);
            row.appendChild(cellaQuantita);

            const cellaTotale = document.createElement('td');
            cellaTotale.dataset.label = 'Subtotale';
            cellaTotale.textContent = `${parseFloat(item.prezzo * item.quantity).toFixed(2).replace('.', ',')} €`;
            row.appendChild(cellaTotale);

            const cellaRimuovi = document.createElement('td');
            cellaRimuovi.dataset.label = 'Rimuovi';
            const buttonRimuovi = document.createElement('button');
            buttonRimuovi.className = 'remove-from-cart';
            buttonRimuovi.dataset.productIdId = item.id;
            buttonRimuovi.textContent = 'X';
            cellaRimuovi.appendChild(buttonRimuovi);
            row.appendChild(cellaRimuovi);

            cartTableBody.appendChild(row);
        });
    }
  }
});
