<?php
session_start();
require_once '../config/config.php';
require_once '../src/core/Database.php';
require_once '../src/core/functions.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
   rispostaJson(false, 'Metodo non consentito.', [], 405);
}


if(!isset($_SESSION['user_id'])){
    rispostaJson(false, 'Devi essere loggato per completare l\'azione', [], 401);
}

// recupero dati spedizione

$nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
$cognome = isset($_POST['cognome']) ? trim($_POST['cognome']) : '';
$indirizzo = isset($_POST['indirizzo']) ? trim($_POST['indirizzo']) : '';
$citta = isset($_POST['citta']) ? trim($_POST['citta']) : '';
$cap = isset($_POST['cap']) ? trim($_POST['cap']) : '';
$note = isset($_POST['note']) ? trim($_POST['note']) : '';

//recupero dati pagamento
$card_number = isset($_POST['card_number']) ? trim($_POST['card_number']) : '';
$expiry_date = isset($_POST['expiry_date']) ? trim($_POST['expiry_date']) : '';
$cvv = isset($_POST['cvv']) ? trim($_POST['cvv']) : '';

if(empty($nome) || empty($cognome) || empty($indirizzo) || empty($citta) || empty($cap)) {
    rispostaJson(false, 'Tutti i campi di spedizione sono obbligatori.', [], 400);
}

if(empty($card_number) || empty($expiry_date) || empty($cvv)) {
    rispostaJson(false, 'Tutti i campi di pagamento sono obbligatori.', [], 400);
    exit();
}

$indirizzo_completo = "$nome $cognome\n$indirizzo\n$cap $citta ($provincia)";

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

    if(empty($cartItem)) {
        throw new Exception('Il carrello è vuoto. Aggiungi prodotti prima di procedere al checkout.');
    }

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

    rispostaJson(true, 'Ordine completato con successo.', ['orderId' => $orderId]);
} catch(Exception $e){
    //annullo tutte le operazioni in caso di qualsiasi errore
    $pdo->rollBack();
    error_log('Errore Checkout:' . $e->getMessage());
    rispostaJson(false, 'Si è verificato un errore durante il completamento dell\'ordine.', [], 500);
}

?>