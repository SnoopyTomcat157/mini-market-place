window.updateCartIcon = async function() {
    try {
        const response = await fetch('api/cart.php?action=get_count');
        const data = await response.json();

        if (data.success) {
            const cartCountSpan = document.getElementById('cart-item-count');
            if (cartCountSpan) {
                // Usiamo "totalItemCount" che il nostro backend ora calcolerà
                const totalItems = data.totalItemCount || 0;
                if (totalItems > 0) {
                    cartCountSpan.textContent = totalItems;
                    cartCountSpan.style.display = 'inline-block';
                } else {
                    cartCountSpan.textContent = '';
                    cartCountSpan.style.display = 'none';
                }
            }
        }
    } catch (error) {
        console.error('Impossibile aggiornare il contatore del carrello:', error);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    
    // Logica per il menu a scomparsa
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const mainNav = document.querySelector('.main-nav');

    if (mobileMenuToggle && mainNav) {
        mobileMenuToggle.addEventListener('click', () => {
            // Aggiunge o rimuove la classe 'is-active' alla navigazione.
            mainNav.classList.toggle('is-active');
        });
    }
    window.updateCartIcon();
});