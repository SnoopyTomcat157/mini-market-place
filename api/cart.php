<?php
session_start();
require_once '../config/config.php';
require_once '../src/core/Database.php';

function getCartState($pdo) {
    $cartItems = [];
    $totalPrice = 0;
    $isUserLoggedIn = isset($_SESSION['user_id']);

    if ($isUserLoggedIn) {
        //utente loggato:
        $sql = "SELECT p.id_prodotto, p.nome_prodotto, p.prezzo, p.nome_file_immagine, cu.quantita
                FROM carrelli_utente cu
                JOIN prodotti p ON cu.id_prodotto = p.id_prodotto
                WHERE cu.id_utente = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['user_id']]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $row) {
            $subtotal = $row['prezzo'] * $row['quantita'];
            $totalPrice += $subtotal;
            $cartItems[] = [
                'id' => $row['id_prodotto'],
                'name' => $row['nome_prodotto'],
                'price' => $row['prezzo'],
                'image' => $row['nome_file_immagine'],
                'quantity' => $row['quantita'],
                'subtotal' => $subtotal
            ];
        }
        $itemCount = count($cartItems);

    } else {
        // utente ospite
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
        $itemCount = count($_SESSION['cart']);
    }
    
    return [
        'cartItems' => $cartItems,
        'totalPrice' => $totalPrice,
        'itemCount' => $itemCount
    ];
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito.']);
    exit();
}

$isUserLoggedIn = isset($_SESSION['user_id']);
$action = isset($_POST['action']) ? $_POST['action'] : '';
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if ($productId <= 0 && in_array($action, ['add', 'update', 'remove'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID prodotto non valido.']);
    exit();
}

try {
    $db = new Database();
    $pdo = $db->getConnection();
    $message = '';

    if ($isUserLoggedIn) {
        // utente loggato
        $userId = $_SESSION['user_id'];
        
        switch ($action) {
            case 'add':
                $sql = "INSERT INTO carrelli_utente (id_utente, id_prodotto, quantita) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantita = quantita + ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$userId, $productId, $quantity, $quantity]);
                $message = 'Prodotto aggiunto al carrello!';
                break;
            case 'update':
                $sql = "UPDATE carrelli_utente SET quantita = ? WHERE id_utente = ? AND id_prodotto = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$quantity, $userId, $productId]);
                $message = 'Carrello aggiornato.';
                break;
            case 'remove':
                $sql = "DELETE FROM carrelli_utente WHERE id_utente = ? AND id_prodotto = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$userId, $productId]);
                $message = 'Prodotto rimosso dal carrello.';
                break;
            default:
                throw new Exception('Azione non valida.');
        }

    } else {
        // utente ospite
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

        switch ($action) {
            case 'add':
                $_SESSION['cart'][$productId] = (isset($_SESSION['cart'][$productId]) ? $_SESSION['cart'][$productId] : 0) + $quantity;
                $message = 'Prodotto aggiunto al carrello!';
                break;
            case 'update':
                if ($quantity > 0) $_SESSION['cart'][$productId] = $quantity;
                else unset($_SESSION['cart'][$productId]);
                $message = 'Carrello aggiornato.';
                break;
            case 'remove':
                unset($_SESSION['cart'][$productId]);
                $message = 'Prodotto rimosso dal carrello.';
                break;
            default:
                throw new Exception('Azione non valida.');
        }
    }

    // ricalcolo lo stato del carrello
    $cartState = getCartState($pdo);

    $response = [
        'success' => true,
        'message' => $message,
        'cartState' => $cartState
    ];

} catch (Exception $e) {
    http_response_code(400);
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

echo json_encode($response);
?>
