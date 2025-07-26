<?php 
session_start();
require_once '../config/config.php';
require_once '../src/core/Database.php';

//funzione per gestire lo stato del carrello

function getCartState($pdo) {
    $cartItems = [];
    $totalPrice = 0;

    if (empty($_SESSION['cart'])) {
        return ['cartItems' => [], 'totalPrice' => 0, 'itemCount' => 0];
    }
    
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
    
    return [
        'cartItems' => $cartItems,
        'totalPrice' => $totalPrice,
        'itemCount' => count($_SESSION['cart'])
    ];
}

header('Content-Type: application/json');

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
    $db = new Database();
    $pdo = $db->getConnection();
    $messaage = '';
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
        case 'update':
            $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

            if ($productId > 0) {
                if ($quantity > 0) {
                    $_SESSION['cart'][$productId] = $quantity;
                } else {
                    unset($_SESSION['cart'][$productId]);
                }
            }
            $message = 'Carrello aggiornato.';
            break;
            
        case 'remove':
            $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            if (isset($_SESSION['cart'][$productId])) {
                unset($_SESSION['cart'][$productId]);
            }
            $message = 'Prodotto rimosso dal carrello.';
            break;

        default:
            throw new Exception('Azione non valida.');
    }

    $cartState = getCartState($pdo);

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