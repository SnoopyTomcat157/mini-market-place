document.addEventListener('DOMContentLoaded', () => {
    const checkoutForm = document.getElementById('checkoutForm');

    if(checkoutForm){
        checkoutForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const submitButton = checkoutForm.querySelector('button[type="submit"]');
            const feedbackDiv = document.getElementById('feedbackMessage');

            submitButton.disabled = true;
            feedbackDiv.textContent = 'Elaborazione dell\'ordine in corso...';
            feedbackDiv.className = 'feedback-message';


            const formData = new FormData();

            try{
                const response = await fetch('api/checkout.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if(!response.ok) {
                    throw new Error(result.message || 'Errore durante l\'elaborazione dell\'ordine');
                }

                feedbackDiv.textContent = result.message + 'Verrai reinderizzato tra poco...';
                feedbackDiv.className = 'feedback-message success';

                //reinderizzamento dopo qualche secondo

                setTimeout(() => {
                    window.location.href = 'order_success.php?order_id=' + result.order_id;
                }, 3000);
            } catch (error) {
                console.error('Erorre:', error);
                feedbackDiv.textContent = error.message;
                feedbackDiv.className = 'feedback-message error';
                submitButton.disabled = false; // riabilito il pulsante in caso di errore
            }
            
        });
    }
});