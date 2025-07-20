<?php 
    session_start();
    require_once 'src/core/Database.php';

    //controllo di sicurezza
    if(!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }

    if(($_SESSION['user_role'] !== 'venditore') && ($_SESSION['user_role'] !== 'admin')) {
        header('Location: error_403_page.php');
        exit();
    }

    //recuoero tutti i prodotti del venditore
    $seller_product = [];
    $errorMessage = null;

    try {
        $database = new Database();
        $pdo = $database->getConnection();
        $sql = "SELECT id_prodotto, nome_prodotto, prezzo, quantita_disponibile, stato_prodotto, nome_file_immagine
                FROM prodotti
                WHERE id_utente_venditore = ?
                ORDER BY data_inserimento DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['user_id']]);
        $seller_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log($e->getMessage());
        $errorMessage = 'Si è verificato un errore durante il recupero dei prodotti. Riprova più tardi.';
    }

    $pageTitle = "Dashboard Venditore - MiniMarketplace";
    require_once 'src/templates/header.php';
?>
    <div class = "dashboard-container">
        <div class="dashboard-header">
            <h1>Dashboard Venditore</h1>
            <a href="add_product.php" class="button-primary">Aggiungi Nuovo Prodotto</a>
        </div>
        <?php if ($errorMessage): ?>
            <p class="feedback-message error"<?php echo $errorMessage?>></p>
        <?php endif; ?>

        <div class="dashboard-content">
            <h2>I Tuoi Prodotti in Vendita</h2>
            <?php if(empty($seller_products)): ?>
                <p>Non sono presenti prodotti in vendita</p>
            <?php else: ?>
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>Immagine</th>
                            <th>Prodotto</th>
                            <th>Prezzo</th>
                            <th>Quantità</th>
                            <th>Stato</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($seller_products as $product): ?>
                            <tr data-product-row-id = "<?php echo $product['id_prodotto']; ?>">
                                <td>
                                    <?php 
                                    $image_name = isset($product['nome_file_immagine']) && !empty($product['nome_file_immagine']) ? $product['nome_file_immagine'] : 'default_image.png';
                                    ?>
                                    <img src="uploads/products/<?php echo htmlspecialchars($image_name); ?>" alt="<?php echo htmlspecialchars($product['nome_prodotto']); ?>" class="dashboard-product-img">
                            </td>
                            <td><?php echo htmlspecialchars($product['nome_prodotto']); ?></td>
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

    <script src="js/seller_dashboard.js"></script>

    <?php
    require_once 'src/templates/footer.php';
    ?>
