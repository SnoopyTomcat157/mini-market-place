<?php
session_start();
require_once 'src/core/functions.php';

assicuraUtenteConRuolo(['admin']);

require_once 'src/core/Database.php';

$products = [];
$errorMessage = null;

try {
    $database = new Database();
    $pdo = $database->getConnection();

    // admin vede tutti i prodotti con il nome del venditore
    $sql = "SELECT p.id_prodotto, p.nome_prodotto, p.prezzo, p.quantita_disponibile, p.stato_prodotto, p.nome_file_immagine, u.username AS nome_venditore
            FROM prodotti p
            JOIN utenti u ON p.id_utente_venditore = u.id_utente
            ORDER BY p.data_inserimento DESC";
    $stmt = $pdo->query($sql);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log($e->getMessage());
    $errorMessage = "Si è verificato un errore nel caricare i prodotti.";
}

$pageTitle = "Pannello Admin";
require_once 'src/templates/header.php';
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>Pannello di Amministrazione Prodotti</h1>
        <a href="add_product.php" class="button-primary">Aggiungi Nuovo Prodotto</a>
    </div>

    <?php if ($errorMessage): ?>
        <p class="feedback-message error"><?php echo $errorMessage; ?></p>
    <?php endif; ?>

    <div class="dashboard-content">
        <h2>Tutti i Prodotti del Marketplace</h2>
        
        <?php if (empty($products)): ?>
            <p>Nessun prodotto presente nel marketplace.</p>
        <?php else: ?>
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Immagine</th>
                        <th>Prodotto</th>
                        <th>Venditore</th> 
                        <th>Prezzo</th>
                        <th>Quantità</th>
                        <th>Stato</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr data-product-row-id="<?php echo $product['id_prodotto']; ?>">
                            <td>
                                <img src="uploads/products/<?php echo htmlspecialchars($product['nome_file_immagine'] ?? 'default_image.png'); ?>" alt="<?php echo htmlspecialchars($product['nome_prodotto']); ?>" class="dashboard-product-img">
                            </td>
                            <td><?php echo htmlspecialchars($product['nome_prodotto']); ?></td>
                            <td><?php echo htmlspecialchars($product['nome_venditore']); ?></td>
                            <td><?php echo number_format($product['prezzo'], 2, ',', '.'); ?> €</td>
                            <td><?php echo htmlspecialchars($product['quantita_disponibile']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($product['stato_prodotto'])); ?></td>
                            <td class="actions">
                                <a href="edit_product.php?id=<?php echo $product['id_prodotto']; ?>" class="action-btn btn-edit">Modifica</a>
                                <button class="action-btn btn-delete" data-product-id="<?php echo $product['id_prodotto']; ?>">Elimina</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script src="js/utils.js"></script>
<script src="js/seller_dashboard.js"></script>

<?php
require_once 'src/templates/footer.php';
?>
