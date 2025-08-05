<?php
require_once '../config/config.php';
require_once 'src/core/Database.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
    exit();
}

if(!isset($_SESSION['user_id'])){
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Utente non autenticato']);
    exit();
}

//recupero dati 

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$valutazione = isset($_POST['valutazione']) ? (int)$_POST['valutazione'] : 0;
$testo_recensione = isset($_POST['testo_recensione']) ? trim($_POST['testo_recensione']) : '';
$user_id = $_SESSION['user_id'];

if($product_id <= 0 || $valutazione < 1 || $valutazione > 5){
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID prodotto non valido o valutazione non compresa tra 1 e 5']);
    exit();
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
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dati non validi. Assicurati di aver selezionato una valutazione.']);
            exit();
        }

        try {
            $sql = "INSERT INTO recensioni (id_prodotto, id_utente_recensore, valutazione, testo_recensione) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$product_id, $_SESSION['user_id'], $valutazione, $testo_recensione]);
            echo json_encode(['success' => true, 'message' => 'Grazie per la tua recensione!']);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Hai già lasciato una recensione per questo prodotto.']);
            } else {
                http_response_code(500);
                error_log('Errore salvataggio recensione: ' . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Si è verificato un errore. Riprova più tardi.']);
            }
        }
        break;

    case 'update':
        $reviewId = isset($_POST['review_id']) ? (int)$_POST['review_id'] : 0;
        $valutazione = isset($_POST['valutazione']) ? (int)$_POST['valutazione'] : 0;
        $testo_recensione = isset($_POST['testo_recensione']) ? trim($_POST['testo_recensione']) : '';

        if ($reviewId <= 0 || $valutazione < 1 || $valutazione > 5) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dati non validi.']);
            exit();
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
            echo json_encode(['success' => true, 'message' => 'Recensione aggiornata con successo.']);
        } else {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Impossibile aggiornare la recensione o permessi non sufficienti.']);
        }
        break;

    case 'delete':
        $reviewId = isset($_POST['review_id']) ? (int)$_POST['review_id'] : 0;
        if ($reviewId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID recensione non valido.']);
            exit();
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
            echo json_encode(['success' => true, 'message' => 'Recensione eliminata con successo.']);
        } else {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Impossibile eliminare la recensione o permessi non sufficienti.']);
        }
        break;
    
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Azione non valida.']);
        break;
}
?>
