<?php
    session_start();
    require_once 'src/core/Database.php';
    require_once 'src/core/functions.php';

    //controllo utente
    assicuraUtenteAutenticato();


    //utente deve essere o venditore o admin
    assicuraUtenteConRuolo(['venditore', 'admin']);

    $categories = [];
    $errorMessage = null;

    //recupero le categorie dal database
    try {
        $database = new Database();
        $pdo = $database->getConnection();
        
        $sql = "SELECT id_categoria, nome_categoria
                FROM categorie
                ORDER BY nome_categoria";
        $stmt = $pdo->query($sql);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log($e->getMessage());
        $errorMessage = "Si è verificato un errore durante il recupero delle categorie. Riprova più tardi.";
    }

    $pageTitle = "Aggiungi Nuovo Prodotto";
    require_once 'src/templates/header.php';
?>

<section class="form-container">
    <h1>Aggiungi Nuovo Prodotto</h1>
    <p>Compila i campi sottostanti per mettere in vendita un nuovo articolo.</p>

    <?php if ($errorMessage): ?>
        <p class="feedback-message error"><?php echo $errorMessage; ?></p>
    <?php endif; ?>

    <form id="addProductForm" enctype="multipart/form-data">
        <div class="form-group">
            <label for="nome_prodotto">Nome Prodotto</label>
            <input type="text" id="nome_prodotto" name="nome_prodotto" required>
        </div>

        <div class="form-group">
            <label for="descrizione">Descrizione Prodotto</label>
            <textarea name="descrizione" id="descrizione" rows="5"></textarea>
        </div>

        <div class="form-group">
            <label for="prezzo">Prezzo (€)</label>
            <input type="number" id="prezzo" name="prezzo" step="0.01" min="0" required>
        </div>

        <div class="form-group">
            <label for="quantita">Quantità Disponibile</label>
            <input type="number" id="quantita" name="quantita" min="0" required>
        </div>
        
        <div class="form-group">
            <label for="categoria_id">Categoria</label>
            <select name="categoria_id" id="categoria_id" required>
                <option value="">-- Seleziona una Categoria --</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category['id_categoria']); ?>">
                        <?php echo htmlspecialchars($category['nome_categoria']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="immagine">Immagine Prodotto</label>
            <input type="file" id="immagine" name="immagine" accept="image/jpeg, image/jpg, image/png, image/gif">
        </div>

        <button type="submit" class="button-primary">Aggiungi Prodotto</button>
    </form>
    <div id="feedbackMessage" class="feedback-message"></div>
</section>

<script src="js/add_product.js"></script>

<?php
    require_once 'src/templates/footer.php';
?>