document.addEventListener('DOMContentLoaded', () => {
    
    // Logica per il menu a scomparsa
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const mainNav = document.querySelector('.main-nav');

    if (mobileMenuToggle && mainNav) {
        mobileMenuToggle.addEventListener('click', () => {
            // Aggiunge o rimuove la classe 'is-active' alla navigazione.
            // Il CSS si occuperà di mostrare/nascondere il menu di conseguenza.
            mainNav.classList.toggle('is-active');
        });
    }
});