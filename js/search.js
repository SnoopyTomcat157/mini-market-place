document.addEventListener('DOMContentLoaded', () => {
    const searchForm = document.getElementById('searchForm');
    const productGridContainer = document.getElementById('productGridContainer');
    const resultsTitle = document.getElementById('resultsTitle');

    if (searchForm) {
        searchForm.addEventListener('submit', (event) => {
            event.preventDefault();

            const formData = new FormData(searchForm);
            const query = formData.get('query');
            const category = formData.get('category');
            
            // Mostro un messaggio di caricamento all'utente
            productGridContainer.innerHTML = '<p>Ricerca in corso...</p>';
            resultsTitle.textContent = 'Risultati della Ricerca';

            // Costruisceol'URL per la chiamata API in modo sicuro
            const apiUrl = `api/search.php?query=${encodeURIComponent(query)}&category=${encodeURIComponent(category)}`;

            // Eseguo la chiamata AJAX
            fetch(apiUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Errore di rete o del server.');
                    }
                    return response.json();
                })
                .then(products => {
                    // Chiamo la funzione per visualizzare i prodotti ricevuti
                    displayProducts(products);
                })
                .catch(error => {
                    console.error('Errore durante la ricerca:', error);
                    productGridContainer.innerHTML = '<p>Si è verificato un errore durante la ricerca. Riprova più tardi.</p>';
                });
        });
    }

    function displayProducts(products) {
        productGridContainer.innerHTML = ''; // Pulisco i risultati precedenti

        if (!products || products.length === 0 || products.error) {
            productGridContainer.innerHTML = '<p>Nessun prodotto trovato per i criteri selezionati.</p>';
            return;
        }

        // Per ogni prodotto, cre0 la sua card HTML e la aggiungo al contenitore
        products.forEach(product => {
            const imageName = product.nome_file_immagine ? product.nome_file_immagine : 'default_image.png';
            const productPrice = parseFloat(product.prezzo).toFixed(2).replace('.', ',');

            const productCardHTML = `
                <div class="product-card">
                    <a href="product_detail.php?id=${product.id_prodotto}">
                        <div class="product-image-container">
                            <img src="uploads/products/${imageName}" alt="${product.nome_prodotto}">
                        </div>
                        <div class="product-info">
                            <h3>${product.nome_prodotto}</h3>
                            <p class="price">${productPrice} €</p>
                        </div>
                    </a>
                </div>
            `;
            productGridContainer.innerHTML += productCardHTML;
        });
    }
});
