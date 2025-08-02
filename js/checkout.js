document.addEventListener('DOMContentLoaded', () => {
    const checkoutForm = document.getElementById('checkoutForm');
    const toPaymentBtn = document.getElementById('toPaymentBtn');
    const finalSubmitBtn = document.getElementById('finalSubmitBtn');
    const shippingStep = document.getElementById('shipping-step');
    const paymentStep = document.getElementById('payment-step');
    const feedbackDiv = document.getElementById('feedbackMessage');

    if (toPaymentBtn) {
        toPaymentBtn.addEventListener('click', () => {
            // validazione form di spedizione
            const shippingInputs = shippingStep.querySelectorAll('input[required]');
            let isShippingValid = true;
            shippingInputs.forEach(input => {
                if (!input.value.trim()) {
                    isShippingValid = false;
                }
            });

            if (!isShippingValid) {
                feedbackDiv.textContent = 'Per favore, compila tutti i campi dell\'indirizzo.';
                feedbackDiv.className = 'feedback-message error';
                return;
            }

            // se valido, nascondo il primo step e mostro il secondo
            shippingStep.classList.remove('is-active');
            paymentStep.classList.add('is-active');

            // nascondo il primo pulsante e mostro quello finale
            toPaymentBtn.style.display = 'none';
            finalSubmitBtn.style.display = 'block';
            feedbackDiv.textContent = ''; // pulisce i messaggi
        });
    }

    if(checkoutForm){
        checkoutForm.addEventListener('submit', async (event) => {
            event.preventDefault();

             // validazione form di pagamento
            const paymentInputs = paymentStep.querySelectorAll('input[required]');
            let isPaymentValid = true;
            paymentInputs.forEach(input => {
                if (!input.value.trim()) {
                    isPaymentValid = false;
                }
            });

            if (!isPaymentValid) {
                feedbackDiv.textContent = 'Per favore, compila tutti i campi del pagamento.';
                feedbackDiv.className = 'feedback-message error';
                return;
            }

            finalSubmitBtn.disabled = true;
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
                finalSubmitBtn.disabled = false; // riabilito il pulsante in caso di errore
            }
            
        });
    }
});