<?php
session_start();
require_once 'src/core/Database.php';


if(!isset($_SESSION['user_id'])){
    header(('Location: login.php'));
    exit();
}

$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : 0;

if($order_id <= 0) {
    header('Location: index.php');
    exit();
}

$pageTitle = "Ordine Completato - MiniMarketplace";
require_once 'src/templates/header.php';
?>

<section class="success-container">
    <svg class="success-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
        <polyline points="22 4 12 14.01 9 11.01"></polyline>
    </svg>

    <h1>Grazie per il tuo ordine!</h1>
    <p>Il tuo acquisto è stato completato con successo.</p>
    <p>Il tuo numero d'ordine è:</p>
    <p class="order-number">#<?php echo htmlspecialchars($order_id); ?></p>
    
    <div class="success-actions">
        <a href="index.php" class="button-secondary">Torna alla Home</a>
        <a href="user_dashboard.php" class="button-primary">Vai ai tuoi ordini</a>
    </div>
</section>

<?php
require_once 'src/templates/footer.php';
?>