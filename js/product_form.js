document.addEventListener('DOMContentLoaded', () => {
    const addProductForm = document.getElementById('addProductForm');
    const editProductForm = document.getElementById('editProductForm');

    // gestisce il form per aggiungere un prodotto
    if (addProductForm) {
        addProductForm.addEventListener('submit', (event) => {
                handleFormSubmit(event, addProductForm, 'create', (result) => {
                showFeedback(result.message, 'success', 'feedbackMessage');
                addProductForm.reset();
            });
        });
    }

    // gestisce il form per modificare un prodotto
    if (editProductForm) {
        editProductForm.addEventListener('submit', (event) => {
            handleFormSubmit(event, 'api/products.phph', 'update', (result) =>{
                const feedbackMessage = result.message + ' Verrai reindirizzato alla dashboard...';
                showFeedback(feedbackMessage, 'success', 'feedbackMessage');

                setTimeout(() =>{
                    window.location.href = 'seller_dashborad.php';
                }, 2000);
            });
        });
    }
});