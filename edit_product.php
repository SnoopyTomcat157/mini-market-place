<?php
session_start();
require_once 'src/core/functions.php';

assicuraUtenteConRuolo(['venditore', 'admin']);

require_once 'src/core/Database.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;
$categories = [];
$errorMessage = null;

if($product_id <= 0) {
    header('Location: seller_dashboard.php');
    exit();
}

try{
    $database = new Database();
    $pdo = $database->getConnection();

    $sql_product = "SELECT *
                    FROM prodotti
                    WHERE id_prodotto = ?";

    $stmt_product = $pdo->prepare($sql_product);
    $stmt_product->execute([$product_id]);
    $product = $stmt_product->fetch(PDO::FETCH_ASSOC);

    if(!$product || ($product['id_utente_venditore'] !== $_SESSION['user_id'] && $_SESSION['user_role'] !== 'admin')) {
        header('Location: error_403.php');
        exit();
    }

    $sql_categories = " SELECT id_categoria, nome_categoria
                        FROM categorie
                        ORDER BY nome_categoria ASC";
    $stmt_categories = $pdo->query($sql_categories);
    $categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);
} catch ( Exception $e){
    error_log($e->getMessage());
    $errorMessage = "Si è verificato un errore durante il recupero del prodotto. Riprova più tardi.";
}

$pageTitle = "Modifica Prodotto";
require_once 'src/templates/header.php';
?>

<section class="form-container">
    <h1>Modifica Prodotto</h1>
    <p>Aggiorna i dettagli del tuo articolo.</p>

    <?php if ($errorMessage): ?>
        <p class="feedback-message error"><?php echo $errorMessage; ?></p>
    <?php elseif ($product): ?>
        <form id="editProductForm" enctype="multipart/form-data">
            <!-- Campo nascosto per inviare l'ID del prodotto -->
            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id_prodotto']); ?>">

            <div class="form-group">
                <label for="nome_prodotto">Nome Prodotto</label>
                <input type="text" id="nome_prodotto" name="nome_prodotto" value="<?php echo htmlspecialchars($product['nome_prodotto']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="descrizione">Descrizione</label>
                <textarea id="descrizione" name="descrizione" rows="5"><?php echo htmlspecialchars($product['descrizione_prodotto']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="prezzo">Prezzo (€)</label>
                <input type="number" id="prezzo" name="prezzo" step="0.01" min="0" value="<?php echo htmlspecialchars($product['prezzo']); ?>" required>
            </div>

            <div class="form-group">
                <label for="quantita">Quantità Disponibile</label>
                <input type="number" id="quantita" name="quantita" min="0" value="<?php echo htmlspecialchars($product['quantita_disponibile']); ?>" required>
            </div>

            <div class="form-group">
                <label for="categoria_id">Categoria</label>
                <select id="categoria_id" name="categoria_id" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['id_categoria']); ?>" <?php if ($category['id_categoria'] == $product['id_categoria']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($category['nome_categoria']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="immagine">Cambia Immagine (opzionale)</label>
                <p>Immagine attuale: <?php echo htmlspecialchars($product['nome_file_immagine'] ?? 'Nessuna'); ?></p>
                <input type="file" id="immagine" name="immagine" accept="image/jpeg, image/jpg, image/png, image/gif">
            </div>

            <button type="submit" class="button-primary">Salva Modifiche</button>
        </form>
        <div id="feedbackMessage" class="feedback-message"></div>
    <?php endif; ?>
</section>

<script src="js/utils.js"></script>
<script src="js/product_form.js"></script>

<?php
require_once 'src/templates/footer.php';
?>

