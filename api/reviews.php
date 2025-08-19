<?php
require_once '../config/config.php';
require_once '../src/core/Database.php';
require_once '../src/core/functions.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    rispostaJson(false, 'Metodo non consentito', [], 405);
}

if(!isset($_SESSION['user_id'])){
    rispostaJson(false, 'Devi essere loggato per eseguire questa azione', [], 401);
    exit();
}

//recupero dati 

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$valutazione = isset($_POST['valutazione']) ? (int)$_POST['valutazione'] : 0;
$testo_recensione = isset($_POST['testo_recensione']) ? trim($_POST['testo_recensione']) : '';
$user_id = $_SESSION['user_id'];

if($product_id <= 0 || $valutazione < 1 || $valutazione > 5){
    rispostaJson(false, 'ID prodotto non valido o valutazione non compresa tra 1 e 5', [], 400);
}
$action = isset($_POST['action']) ? $_POST['action'] : '';
$database = new Database();
$pdo = $database->getConnection();

switch ($action) {
    case 'create':
        $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $valutazione = isset($_POST['valutazione']) ? (int)$_POST['valutazione'] : 0;
        $testo_recensione = isset($_POST['testo_recensione']) ? trim($_POST['testo_recensione']) : '';

        if ($product_id <= 0 || $valutazione < 1 || $valutazione > 5) {
            rispostaJson(false, 'Dati non validi. Assicurati di aver selezionato una valutazione.', [], 400);
        }

        try {
            $sql = "INSERT INTO recensioni (id_prodotto, id_utente_recensore, valutazione, testo_recensione) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$product_id, $_SESSION['user_id'], $valutazione, $testo_recensione]);
            rispostaJson(true, 'Grazie per la tua recensione!');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                rispostaJson(false, 'Hai già lasciato una recensione per questo prodotto.', [], 409);
            } else {
                error_log('Errore salvataggio recensione: ' . $e->getMessage());
                rispostaJson(false, 'Si è verificato un errore. Riprova più tardi.', [], 500);
            }
        }
        break;

    case 'update':
        $reviewId = isset($_POST['review_id']) ? (int)$_POST['review_id'] : 0;
        $valutazione = isset($_POST['valutazione']) ? (int)$_POST['valutazione'] : 0;
        $testo_recensione = isset($_POST['testo_recensione']) ? trim($_POST['testo_recensione']) : '';

        if ($reviewId <= 0 || $valutazione < 1 || $valutazione > 5) {
            rispostaJson(false, 'Dati non validi. Assicurati di aver selezionato una valutazione.', [], 400);
        }

        $sql = "UPDATE recensioni SET valutazione = ?, testo_recensione = ? WHERE id_recensione = ?";
        $params = [$valutazione, $testo_recensione, $reviewId];

        // utente non admin può modificare solo le proprie recensioni
        if ($_SESSION['user_role'] !== 'admin') {
            $sql .= " AND id_utente_recensore = ?";
            $params[] = $_SESSION['user_id'];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            rispostaJson(true, 'Recensione aggiornata con successo.');
        } else {
            rispostaJson(false, 'Impossibile aggiornare la recensione o permessi non sufficienti.', [], 403);
        }
        break;

    case 'delete':
        $reviewId = isset($_POST['review_id']) ? (int)$_POST['review_id'] : 0;
        if ($reviewId <= 0) {
            rispostaJson(false, 'ID recensione non valido.', [], 400);
        }

        $sql = "DELETE FROM recensioni WHERE id_recensione = ?";
        $params = [$reviewId];

        // utente non admin può eliminare solo le proprie recensioni
        if ($_SESSION['user_role'] !== 'admin') {
            $sql .= " AND id_utente_recensore = ?";
            $params[] = $_SESSION['user_id'];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            rispostaJson(true, 'Recensione eliminata con successo.');
        } else {
            rispostaJson(false, 'Impossibile eliminare la recensione o permessi non sufficienti.', [], 403);
        }
        break;
    
    default:
        rispostaJson(false, 'Azione non valida.', [], 400);
        break;
}
?>
