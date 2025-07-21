<?php
    session_start();
    require_once 'src/core/Database.php';

    $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if($product_id <= 0) {
        http_response_code(404);
        include('error_404.php');
        exit();
    }

    $product = null;
    $errorMessage = null;

    try {
        $database = new Database();
        $pdo = $database->getConnection();
        $sql = "SELECT p.*, c.nome_categoria, u.username AS nome_venditore
                FROM prodotti p
                LEFT JOIN categorie c ON p.id_categoria = c.id_categoria
                LEFT JOIN utenti u ON p.id_venditore = u.id_utente
                WHERE p.id_prodotto = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        //se non viene restituito un prodotto la query non esiste
        if(!$product) {
            http_response_code(404);
            include('error_404.php');
            exit();
        }
    } catch (Exception $e) {
        error_log(e->getMessage());
        $errorMessage = "Si è verificato un errore nel caricare i dettagli del prodotto. Riprova più tardi.";
    }
    
    $pageTitle = isset($product['nome_prodotto']) ? htmlspecialchars($product['nome_prodotto']) : 'Dettagli Prodotto';
    require_once 'src/templates/header.php';
?>

<section class="product-detail-conteiner">
    <?php if ($errorMessage): ?>
        <p class="feedback-message error"><?php echo $errorMessage; ?></p>
    <?php elseif($product): ?>
        <div class="product-detail-image">
            <?php
                $image_name = isset($product['nome_file_immagine']) && !empty($product['nome_file_immagine'])
                              ? $product['nome_file_immagine'] 
                              : 'default_image.png';
            ?>
            <img src="uploads/products/<?php echo htmlspecialchars($image_name); ?>" alt="<?php echo htmlspecialchars($product['nome_prodotto']); ?>">
        </div>

        <div class="product-detail-info">
            <h1><?php echo htmlspecialchars($product['nome_prodotto']); ?></h1>
            <p class="price"><?php echo number_format($product['prezzo'], 2, '.', '.'); ?>€</p>

            <div class="product-meta">
                <p><strong>Venduto da:</strong> <?php echo htmlspecialchars($product['nome_venditore']); ?></p>
                <p><strong>Categoria:</strong> <?php echo htmlspecialchars($product['nome_categoria']); ?></p>
            </div>

            <div class="product-description">
                <h2>Descrizione</h2>
                <p><?php echo nl2br(htmlspecialchars($product['descrizione'])); ?></p>
            </div>

            <div class="product-actions">
                <input type="number" id="quantityInput" value="1" min="1" max="<?php echo htmlspecialchars($product['quantita_disponibile']); ?>" class="quantity-input">
                <button id="addToCartButton" class="button-primary" data-product-id="<?php echo $product['id_prodotto']; ?>">Aggiungi al Carrello</button>
            </div>
            <div id="feedbackMessage" class="feedback-message"></div>
        </div>
        <?php endif; ?>
</section>

<script src="js/cart.js"></script>

<?php
    require_once 'src/templates/footer.php';
?>