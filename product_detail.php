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
                LEFT JOIN utenti u ON p.id_utente_venditore = u.id_utente
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

        //recupero recensioni
        $sql_reviews = "SELECT r.*, u.username
                        FROM recensioni r
                        JOIN utenti u on r.id_utente_recensore = u.id_utente
                        WHERE r.id_prodotto = ?
                        ORDER BY r.data_recensione DESC";
        $stmt_reviews = $pdo->prepare($sql_reviews);
        $stmt_reviews->execute([$product_id]);
        $reviews = $stmt_reviews->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        error_log($e->getMessage());
        $errorMessage = "Si è verificato un errore nel caricare i dettagli del prodotto. Riprova più tardi.";
    }
    
    $pageTitle = isset($product['nome_prodotto']) ? htmlspecialchars($product['nome_prodotto']) : 'Dettagli Prodotto';
    require_once 'src/templates/header.php';
?>

<link rel="stylesheet" href="css/reviews.css">

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
            <p class="price"><?php echo number_format($product['prezzo'], 2, ',', '.'); ?>€</p>

            <div class="product-meta">
                <p><strong>Venduto da:</strong> <?php echo htmlspecialchars($product['nome_venditore']); ?></p>
                <p><strong>Categoria:</strong> <?php echo htmlspecialchars($product['nome_categoria']); ?></p>
            </div>

            <div class="product-description">
                <h2>Descrizione</h2>
                <p><?php echo nl2br(htmlspecialchars($product['descrizione_prodotto'])); ?></p>
            </div>

            <div class="product-actions">
                <input type="number" id="quantityInput" value="1" min="1" max="<?php echo htmlspecialchars($product['quantita_disponibile']); ?>" class="quantity-input">
                <button id="addToCartBtn" class="button-primary" data-product-id="<?php echo $product['id_prodotto']; ?>">
                    <span class="button-text"> Aggiungi al Carrello </span>
                </button>
            </div>
       
        <?php endif; ?>
</section>

<section class="reviews-contaier">
    <h2>Recensioni</h2>
    <div class="review-form-wrapper">
        <!-- form per lasciare recensioni, visibile solo se loggati-->
        <?php if(isset($_SESSION['user_id'])): ?>
            <form id="review-form">
                <h3>Scrivi una recensione</h3>
                <div class="form-group">
                    <label for="valutazione">Valatuazione</label>
                    <select name="valutazione" id="valutazione" required>
                        <option value="">-- Seleziona un voto</option>
                        <option value="5">5</option>
                        <option value="4">4</option>
                        <option value="3">3</option>
                        <option value="2">2</option>
                        <option value="1">1</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="testo_recensione">La tua recensione</label>
                    <textarea name="testo_recensione" id="testo_recensione" rows="4" required></textarea>
                </div>
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
            </form>
            <div id="reviewFeedback" class="feedback-message"></div>
            <?php else: ?>
                <p>Devi aver eseguito <a href="login.php"> l'accesso </a> per lasciare una recensione.</p>
        <?php endif; ?>
    </div>

    <div class="reviews-list">
        <?php if(empty($reviews)): ?>
            <p>Non sono ancora presenti recensioni sul prodotto.</p>
        <?php else: ?>
            <?php foreach($reviews as $review): ?>
                <div class="review-card" data-review-id="<?php echo $review['id_recensione']; ?>">
                    <div class="review-header">
                        <strong><?php echo htmlspecialchars($review['username']);?></strong>
                        <span class="review-date"><?php echo date('d/m/Y', strtotime($review['data_recensione'])); ?></span>
                    </div>
                    <div class="review-rating" data-current-rating="<?php echo $review['valutazione']; ?>"></div>
                    <strong>Valutazione: <?php echo htmlspecialchars($review['valutazione']); ?></strong>
                    <div class="review-body">
                        <p><?php echo nl2br(htmlspecialchars($review['testo_recensione'])); ?></p>
                    </div>
                    <?php
                    // --- NUOVA LOGICA: Mostra i pulsanti solo al proprietario o all'admin ---
                    if (isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $review['id_utente_recensore'] || $_SESSION['user_role'] === 'admin')):
                    ?>
                        <div class="review-actions">
                            <button class="action-btn btn-edit-review" data-review-id="<?php echo $review['id_recensione']; ?>">Modifica</button>
                            <button class="action-btn btn-delete-review" data-review-id="<?php echo $review['id_recensione']; ?>">Elimina</button>
                        </div>
                        <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<script src="js/cart.js"></script>
<script src="js/reviews.js"></script>

<?php
    require_once 'src/templates/footer.php';
?>