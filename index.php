<?php

session_start();

require_once 'src/core/Database.php';

$products = [];
$errorMessage = null;

try {
    $db = new Database();
    $pdo = $db->getConnection();

    /*query per mostrare gli ultimi 8 prodotti inseriti dagli utenti*/

    $sql ="SELECT id_prodotto, nome_prodotto, prezzo, nome_file_immagine
            FROM prodotti
            ORDER BY id_prodotto DESC
            LIMIT 8";

    $stmt = $pdo->query($sql);

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $errorMessage = "Errore durante il caricamento dei prodotti: " . $e->getMessage();
}

//header della pagina
$pageTitle = "Benvenuti su MiniMarketplace";
require_once 'src/templates/header.php';
?>

    <section class = "sezione-benvenuto">
        <h1>Benvenuto sul tuo nuovo marketplace di fiducia!</h1>
        <p>Scopri migliaia di prodotti messi in vendita da utenti come te.</p>
    </section>

    <section class = "sezione-prodotti">
        <h2>Ultimi Prodotti Aggiunti</h2>

    <?php if($errorMessage): ?>
        <div class = "error-message">
            <?php echo $errorMessage; ?>
        </div>
    <?php elseif (empty($products)): ?>
        <p>Nessun prodotto disponibile al momento. Torna a trovarci presto!</p>
    <?php else: ?>
        <div class =product-grid>
            <?php foreach ($products as $product): ?>
                <?php include 'src/templates/product_card.php'; ?>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

<?php 
//footer
require_once 'src/templates/footer.php';
?> 