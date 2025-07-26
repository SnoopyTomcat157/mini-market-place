<?php
session_start();
require_once 'src/core/Database.php';

$cartItems = [];
$totalPrice = 0;
$errorMessage = null;
$isUserLoggedIn = isset($_SESSION['user_id']);

try {
    $database = new Database();
    $pdo = $database->getConnection();

    if ($isUserLoggedIn) {
        // se utente loggato recupero carrello dal database
        $sql = "SELECT p.id_prodotto, p.nome_prodotto, p.prezzo, p.nome_file_immagine, cu.quantita
                FROM carrelli_utente cu
                JOIN prodotti p ON cu.id_prodotto = p.id_prodotto
                WHERE cu.id_utente = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['user_id']]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $row) {
            $subtotal = $row['prezzo'] * $row['quantita'];
            $totalPrice += $subtotal;
            $cartItems[] = [
                'id' => $row['id_prodotto'],
                'name' => $row['nome_prodotto'],
                'price' => $row['prezzo'],
                'image' => $row['nome_file_immagine'],
                'quantity' => $row['quantita'],
                'subtotal' => $subtotal
            ];
        }

    } else {
        // se utente ospite recupero carrello dalla sessione
        if (!empty($_SESSION['cart'])) {
            $productIds = array_keys($_SESSION['cart']);
            $placeholders = implode(',', array_fill(0, count($productIds), '?'));
            $sql = "SELECT id_prodotto, nome_prodotto, prezzo, nome_file_immagine FROM prodotti WHERE id_prodotto IN ($placeholders)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($productIds);
            $productsFromDb = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($productsFromDb as $product) {
                $quantity = $_SESSION['cart'][$product['id_prodotto']];
                $subtotal = $product['prezzo'] * $quantity;
                $totalPrice += $subtotal;
                $cartItems[] = [
                    'id' => $product['id_prodotto'],
                    'name' => $product['nome_prodotto'],
                    'price' => $product['prezzo'],
                    'image' => $product['nome_file_immagine'],
                    'quantity' => $quantity,
                    'subtotal' => $subtotal
                ];
            }
        }
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    $errorMessage = "Si è verificato un errore nel caricare i dati del carrello.";
}

$pageTitle = "Il Tuo Carrello";
require_once 'src/templates/header.php';
?>

<section class="cart-container">
    <h1>Il Tuo Carrello</h1>
    <?php if ($errorMessage): ?>
        <p class="feedback-message error"><?php echo $errorMessage; ?></p>
    <?php elseif(empty($cartItems)): ?>
        <div class="cart-empty">
            <p>Il tuo carrello è vuoto.</p>
            <a href="index.php" class="button-primary">Continua lo shopping</a>
        </div>
    <?php else: ?>
        <div class="cart-content">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th colspan="2">Prodotto</th>
                        <th>Prezzo</th>
                        <th>Quantità</th>
                        <th>Subtotale</th>
                        <th>Rimuovi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($cartItems as $item): ?>
                        <tr>
                            <td data-label="Immagine">
                                <?php
                                    $image_name = isset($item['image']) && !empty($item['image']) ? $item['image'] : 'default_image.png';
                                ?>
                                <img src="uploads/products/<?php echo htmlspecialchars($image_name); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-product-img">
                            </td>
                            <td data-label="Prodotto"><?php echo htmlspecialchars($item['name']); ?></td>
                            <td data-label="Prezzo"><?php echo number_format($item['price'], 2, ',', '.'); ?> €</td>
                            <td data-label="Quantità">
                                <input type="number" value="<?php echo $item['quantity']; ?>" min="1" class="update-quantity" data-product-id="<?php echo $item['id']; ?>">
                            </td>
                            <td data-label="Subtotale"><?php echo number_format($item['subtotal'], 2, ',', '.'); ?> €</td>
                            <td data-label="Rimuovi">
                                <button class="remove-from-cart" data-product-id="<?php echo $item['id']; ?>">X</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="cart-summary">
                <h2>Riepilogo Ordine</h2>
                <div class="summary-row">
                    <span>Totale</span>
                    <strong><?php echo number_format($totalPrice, 2, ',', '.'); ?> €</strong>
                </div>
                <a href="checkout.php" class="button-primary checkout-btn">Procedi al Checkout</a>
            </div>
        </div>
    <?php endif; ?>
</section>

<script src="js/cart_page_actions.js"></script>

<?php
require_once 'src/templates/footer.php';
?>