<?php
require_once '../config/config.php';
require_once '../src/core/Database.php';
require_once '../src/core/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    rispostaJson(false, 'Metodo non consentito.', [], 405);
}

// Recupero i parametri di ricerca dall'URL
$query_param = isset($_GET['query']) ? trim($_GET['query']) : '';
$category_param = isset($_GET['category']) ? (int)$_GET['category'] : 0;

$products_found = [];

try {
    $db = new Database();
    $pdo = $db->getConnection();

    // Seleziono solo i prodotti "disponibili"
    $sql = "SELECT id_prodotto, nome_prodotto, prezzo, nome_file_immagine FROM prodotti WHERE stato_prodotto = 'disponibile'";
    

    $params = [];

    if (!empty($query_param)) {
        $sql .= " AND (nome_prodotto LIKE ? OR descrizione_prodotto LIKE ?)";
        $params[] = '%' . $query_param . '%';
        $params[] = '%' . $query_param . '%';
    }

    if ($category_param > 0) {
        $sql .= " AND id_categoria = ?";
        $params[] = $category_param;
    }
    
    $sql .= " ORDER BY data_inserimento DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    $products_found = $stmt->fetchAll(PDO::FETCH_ASSOC);

    rispostaJson(true, 'Prodotti trovati', ['products' => $products_found]);

} catch (Exception $e) {
    // In caso di errore, lo loggo e restituisco un errore JSON
    error_log('Errore di ricerca: ' . $e->getMessage());
    rispostaJson(false, 'Si è verificato un errore. Riprova più tardi.', [], 500);
}
?>