<?php
session_start();
require_once '../config/config.php';
require_once '../src/core/Database.php';

header('Content-TYpe: application/json');

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito.']);
    exit();
}


if(!isset($_SESSION['user_id'])){
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Devi essere loggato per completare l\'azione']);
    exit();
}

// recupero dati

$nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
$cognome = isset($_POST['cognome']) ? trim($_POST['cognome']) : '';
$indirizzo = isset($_POST['indirizzo']) ? trim($_POST['indirizzo']) : '';
$citta = isset($_POST['citta']) ? trim($_POST['citta']) : '';
$cap = isset($_POST['cap']) ? trim($_POST['cap']) : '';
$note = isset($_POST['note']) ? trim($_POST['note']) : '';

$database = new Database();
$pdo = $database->getConnection();

try{
    $pdo->beginTransaction();

    //recupero i prodotti
    $sql_cart = "SELECT p.id_prodotto, p.prezzzo, cu.quantita
                 FROM carrelli_utente cu
                 JOIN prodotti p ON cu.id_prodotto = p.id_prodotto
                 WHERE cu.id_utente = ?";
    $stmt_cart = $pdo->prepare($sql_cart);
    $stmt_cart->execute([$_SESSION['user_id']]);
    $cartItem = $stmt_cart->fetchAll(PDO::FETCH_ASSOC);

    $totalPrice = 0;
    foreach($cartItem as $item) {
        $totalPrice += $item['prezzzo'] * $item['quantita'];
    }

    //inserisco l'ordine nella tabella ordini
    $sql_order = "INSERT INTO ordini(id_utente_acquirente, importo_totale_ordine, indirizzo_spedizione, note_ordine)
                  VALUES (?, ?, ?, ?)";
    $stmt_order = $pdo->prepare(($sql_order));
    $stmt_order->execute([$_SESSION['user_id'], $totalPrice, $indirizzo_completo, $note]);

    //id ultimo ordine
    $orderId = $pdo->lastInsertId();

    //inserisco ogni prodoto nel carrello nella tabella articoli_ordine
    $sql_items = "INSERT INTO articoli_ordine(id_ordine, id_prodotto, quantita_ordinata, prezzo_al_momento_acquisto)
                  VALUES(?, ?, ?, ?)";
    $stmt_items = $pdo->prepare($sql_items);
    
    foreach($cartItem as $item){
        $stmt_items->execute([$orderId, $item['id_prodotto'], $item['quantita'], $item['prezzzo']]);
    }

    //svuto il carrello
    $sqlClearCart = "DELETE FROM carrelli_utente WHERE id_utente = ?";
    $stmtClearCart = $pdo->prepare($sqlClearCart);
    $stmtClearCart->execute($_SESSION['user_id']);


    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Ordine completato con successo.', 'orderId' => $orderId]);
} catch(Exception $e){
    //annullo tutte le operazioni in caso di qualsiasi errore
    $pdo->rollBack();
    http_response_code(500);
    error_log('Errore Checkout:' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Si è verificato un errore durante il completamento dell\'ordine.']);
}

?>