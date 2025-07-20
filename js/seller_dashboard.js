// a differenza degli altri file qui ho usato async/await per sperimentare

document.addEventListener('DOMContentLoaded', () => {
    const productTable = document.querySelector('.product-table');

    if (productTable){
        productTable.addEventListener('click', async (event) => {
            if(event.target && event.target.classList.contains('btn-delete')) {
                const button = event.target;
                const productId = button.dataset.productId;
                const productRow = button.closest('tr');

                if(confirm('Sei sicuro di voler eliminare questo prodotto? L\'azione è irreversibile.')) {
                    const formData = new FormData();
                    formData.append('product_id', productId);
                    formData.append('action', 'delete');

                    try{
                        const response = await fetch('/api/products', {
                            method: 'POST',
                            body: formData
                        });

                        const data = await response.json();
                        if (data.success){
                            if(productRow){
                                productRow.style.transition = 'opacity 0.5s ease';
                                productRow.style.opacity = '0';
                                setTimeout(() => {
                                    productRow.remove();
                                }, 500); //dopo l'animazione l'oggetto viene rimosso
                            }
                            alert(data.message);
                        }  else {
                            alert('Errore durante l\'eliminazione del prodotto: ' + data.message);
                        }
                            
                    } catch (error) {
                        console.error('Errore di rete:', error);
                        alert('Si è verificato un errore di comunicazione col server. Per favore riprova più tardi.');
                    }
                }
            }
        });
    }
});