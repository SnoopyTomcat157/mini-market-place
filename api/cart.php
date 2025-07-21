<?php 
session_start();

header('Content-Type: apllication/json');

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
    exit();
}

if(!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

try{
    switch($action){
        case 'add':
            $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

            if($productId <= 0 || $quantity <= 0) {
                throw new Exception('ID prodotto o quantità non validi.');
            }

            //se già nel carrello aggiorno la quantità

            if(isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId] += $quantity;
            } else {
                $_SESSION['cart'][$productId] = $quantity;
            }

            $message = 'Prodotto aggiunto al carrello con successo!';
            break;

        default:
            throw new Exception('Azione non valida.');
    }

    $response = [
        'success' => true,
        'message' => $message,
        'item_count' => count($_SESSION['cart']) // conteggio totale degli articoli nel carrello
    ];
} catch(Exception $e) {
    http_response_code(400);
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

echo json_encode($response);
?>