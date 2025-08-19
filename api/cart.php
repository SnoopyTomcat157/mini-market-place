<?php
session_start();
require_once '../config/config.php';
require_once '../src/core/Database.php';

/**
 * Funzione helper per calcolare e restituire lo stato completo del carrello.
 * @param PDO $pdo - L'oggetto della connessione al database.
 * @return array - Un array con gli articoli, il prezzo totale e il numero totale di articoli.
 */
function getCartState($pdo) {
    $cartItems = [];
    $totalPrice = 0;
    $totalItemCount = 0;
    $isUserLoggedIn = isset($_SESSION['user_id']);

    if ($isUserLoggedIn) {
        // Logica per utente loggato: recupera dal DB
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
            $totalItemCount += $row['quantita'];
            $cartItems[] = [
                'id' => $row['id_prodotto'],
                'name' => $row['nome_prodotto'],
                'price' => $row['prezzo'],
                'image' => $row['nome_file_immagine'],
                'quantity' => $row['quantita'],
                'subtotal' => $subtotal
            ];
        }
    } else {
        // Logica per utente ospite: recupera dalla sessione
        if (empty($_SESSION['cart'])) {
            return ['cartItems' => [], 'totalPrice' => 0, 'totalItemCount' => 0];
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
        $totalItemCount = array_sum($_SESSION['cart']);
    }
    
    return [
        'cartItems' => $cartItems,
        'totalPrice' => $totalPrice,
        'totalItemCount' => $totalItemCount
    ];
}

header('Content-Type: application/json');
$requestMethod = $_SERVER['REQUEST_METHOD'];

try {
    $db = new Database();
    $pdo = $db->getConnection();

    if ($requestMethod === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_count') {
        $cartState = getCartState($pdo);
        rispostaJson(true, 'Conteggio carrello recuperato', ['totalItemCount' => $cartState['totalItemCount']]);
    }

    if ($requestMethod === 'POST') {
        $isUserLoggedIn = isset($_SESSION['user_id']);
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        $message = '';

        if ($productId <= 0 && in_array($action, ['add', 'update', 'remove'])) {
            rispostaJson(false, 'ID prodotto non valido.', [], 400);
        }

        if ($isUserLoggedIn) {
            // Logica per utente loggato (database)
            $userId = $_SESSION['user_id'];
            switch ($action) {
                case 'add':
                    $sql = "INSERT INTO carrelli_utente (id_utente, id_prodotto, quantita) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantita = quantita + ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$userId, $productId, $quantity, $quantity]);
                    $message = 'Prodotto aggiunto al carrello!';
                    break;
                case 'update':
                    if ($quantity > 0) {
                        $sql = "UPDATE carrelli_utente SET quantita = ? WHERE id_utente = ? AND id_prodotto = ?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$quantity, $userId, $productId]);
                    } else {
                        $sql = "DELETE FROM carrelli_utente WHERE id_utente = ? AND id_prodotto = ?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$userId, $productId]);
                    }
                    $message = 'Carrello aggiornato.';
                    break;
                case 'remove':
                    $sql = "DELETE FROM carrelli_utente WHERE id_utente = ? AND id_prodotto = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$userId, $productId]);
                    $message = 'Prodotto rimosso dal carrello.';
                    break;
                default:
                    rispostaJson(false, 'Azione non valida.', [], 400);
            }
        } else {
            // Logica per utente ospite (sessione)
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
                    rispostaJson(false, 'Azione non valida.', [], 400);
            }
        }

        // Dopo ogni azione, ricalcola lo stato completo del carrello
        $cartState = getCartState($pdo);
        rispostaJson(true, $message, ['cartState' => $cartState]);
        exit();
    }

    rispostaJson(false, 'Metodo o azione non supportati.', [], 405);

} catch (Exception $e) {
   error_log('Errore carrello: ' . $e->getMessage());
   rispostaJson(false, 'Si è verificato un errore. Riprova più tardi.', [], 500);
}
?>
