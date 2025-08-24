<?php
session_start();
require_once 'src/core/functions.php';

//solo gli utenti loggati con ruolo 'acquirente' possono vedere questa pagina.
assicuraUtenteConRuolo(['acquirente']);

$pageTitle = "Diventa un Venditore";
require_once 'src/templates/header.php';
?>

<link rel="stylesheet" href="css/become_seller.css"> 

<section class="dashboard-container">
    <div class="dashboard-header">
        <h1>Diventa un Venditore</h1>
    </div>
    
    <div class="dashboard-content">
        <p>Sei pronto a trasformare i tuoi oggetti in guadagni? Unisciti alla nostra community di venditori!</p>
    
        <div class="benefits" style="margin: 20px 0;">
            <h3>Vantaggi</h3>
            <ul>
                <li>Raggiungi migliaia di potenziali clienti.</li>
                <li>Gestisci i tuoi prodotti da una dashboard semplice e intuitiva.</li>
                <li>Nessun costo di iscrizione.</li>
            </ul>
        </div>

        <form id="becomeSellerForm">
            <p>Cliccando su "Conferma", il tuo account verrà aggiornato a "Venditore" e potrai iniziare a caricare i tuoi prodotti.</p>
            <button type="submit" class="button-primary" style="margin-top: 20px;">Conferma e Diventa Venditore</button>
        </form>
        <div id="feedbackMessage" class="feedback-message"></div>
    </div>
</section>

<script src="js/utils.js"></script>
<script src="js/become_seller.js"></script>

<?php
require_once 'src/templates/footer.php';
?>
