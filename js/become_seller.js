document.addEventListener('DOMContentLoaded', () => {
    const becomeSellerForm = document.getElementById('becomeSellerForm');

    if(becomeSellerForm) {
        becomeSellerForm.addEventListener('submit', (e) => {
            handleFormSubmit(e, 'api/user_actions.php', 'become_seller', (result) => {
                const feedbackMessage = result.message + ' Verrai reindirizzato alla dashboard del venditore...';
                showFeedback(feedbackMessage, 'success', 'feedbackMessage');

                setTimeout(() => {
                    window.location.href = 'seller_dashboard.php';
                }, 3000);
            });
        });
    }
})