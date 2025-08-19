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
               showFeedback('Per favore, compila tutti i campi dell\'indirizzo.', 'error');
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
        checkoutForm.addEventListener('submit', (event) => {
             // validazione form di pagamento
            const paymentInputs = paymentStep.querySelectorAll('input[required]');
            let isPaymentValid = true;
            paymentInputs.forEach(input => {
                if (!input.value.trim()) {
                    isPaymentValid = false;
                }
            });

            if (!isPaymentValid) {
                event.preventDefault();
                showFeedback('Per favore, compila tutti i campi del pagamento.', 'error');
            }
        handleFormSubmit(event, 'api/checkout.php', 'place_order', (result) => {
            const feedbackMessage = result.message + ' Verrai reindirizzato tra poco...';
            showFeedback(feedbackMessage, 'success');

            setTimeout(() => {
                window.location.href = 'order_success.php?order_id=' + result.order_id;
            }, 3000);
        });
            
        });
    }
});