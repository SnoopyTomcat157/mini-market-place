<?php
session_start();
require_once '../src/core/Database.php';

if(!isset($_SESSION['user_id'])){
    header('Location: login.php?redirect=checkout');
    exit();
}

$cartItem = [];
$totalPrice = 0;
$errorMessage = null;

try{
    $database = new Database();
    $pdo = $database->getConnection();

    $sql = "SELECT p.id_prodotto, p.nome_prodotto, p.prezzo, p.nome_file_immagine, cu.quantita
            FROM carrelli_utente cu
            JOIN prodotti p on cu.id_prodotto = p.id_prodotto
            WHERE cu.id_utente = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(empty($result)){
        header('Location: cart_page.php');
        exit();
    }

    foreach($result as $row){
        $total = $row['prezzo'] * $row['quantita'];
        $totalPrice += $total;
        $cartItem[] = [
            'name' => $row['nome_prodotto'],
            'image' => $row['nome_file_immagine'],
            'quantity' => $row['quantita'],
            'subtotal' => $total 
        ];
    }
} catch (Exception $e){
    error_log($e->getMessage());
    $errorMessage = "Si è verificato un errore durante il recupero dei dati del carrello. Riprova più tardi.";
}

$pageTitle = "Checkout";

require_once 'src/templates/header.php';

?>

<link rel="stylesheet" href="css/checkout.css">

<section class="checkout-container">
    <h1>Checkout</h1>
    <?php if($errorMessage): ?>
        <p class="feedback-message error"><?php echo $errorMessage; ?></p>
    <?php else: ?>
          <div class="checkout-layout">
            <!-- Colonna sinistra: Form per i dati di spedizione -->
            <div class="shipping-form">
                <h2>Indirizzo di Spedizione</h2>
                <form id="checkoutForm">
                    <div class="form-group">
                        <label for="nome_completo">Nome Completo</label>
                        <input type="text" id="nome_completo" name="nome_completo" required>
                    </div>
                    <div class="form-group">
                        <label for="indirizzo">Indirizzo (Via, N. civico)</label>
                        <input type="text" id="indirizzo" name="indirizzo" required>
                    </div>
                    <div class="form-group">
                        <label for="citta">Città</label>
                        <input type="text" id="citta" name="citta" required>
                    </div>
                    <div class="form-group">
                        <label for="cap">CAP</label>
                        <input type="text" id="cap" name="cap" required>
                    </div>
                    <div class="form-group">
                        <label for="provincia">Provincia</label>
                        <input type="text" id="provincia" name="provincia" required>
                    </div>
                    <div class="form-group">
                        <label for="note">Note per la consegna (opzionale)</label>
                        <textarea id="note" name="note" rows="3"></textarea>
                    </div>
                </form>
            </div>

            <!-- Colonna destra: Riepilogo dell'ordine -->
            <div class="order-summary">
                <h2>Riepilogo Ordine</h2>
                <ul class="summary-items-list">
                    <?php foreach ($cartItems as $item): ?>
                        <!-- MODIFICA: Aggiunta l'immagine e una struttura migliore -->
                        <li class="summary-item">
                            <img src="uploads/products/<?php echo htmlspecialchars($item['image'] ?? 'default_image.png'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="summary-item-img">
                            <div class="summary-item-details">
                                <span><?php echo htmlspecialchars($item['name']); ?></span>
                                <small>Quantità: <?php echo htmlspecialchars($item['quantity']); ?></small>
                            </div>
                            <span class="summary-item-price"><?php echo number_format($item['subtotal'], 2, ',', '.'); ?> €</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="summary-total">
                    <strong>Totale</strong>
                    <strong><?php echo number_format($totalPrice, 2, ',', '.'); ?> €</strong>
                </div>
                <button type="submit" form="checkoutForm" class="button-primary checkout-btn">Completa Ordine</button>
                <div id="feedbackMessage" class="feedback-message" style="margin-top: 15px;"></div>
            </div>
        </div>
    <?php endif; ?>
</section>

<script src="js/checkout.js"></script>
<?php
require_once 'src/templates/footer.php';
?>