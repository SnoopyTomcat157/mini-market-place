<?php
session_start();

if(isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION = array(); //svuoto la sessione

    //cancello cookie di sessione
    if(ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, 
            $params["path"], $params["domain"],
          $params["secure"], $params["httponly"]);
    }
    session_destroy(); //distruggo la sessione

    header('Location: ../login.php'); //reinderizzo alla pagina di login
    exit();
}

require_once '../config/config.php';
require_once '../src/core/Database.php';
require_once '../src/core/functions.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    rispostaJson(false, 'Metodo non consentito.', [], 405);
}

$action = isset($_POST['action']) ? trim($_POST['action']) : '';

if ($action === 'register') {
//recupero i dati e li sanitizzo
    $user = isset($_POST['username']) ? trim($_POST['username']) : '' ;
    $pw = isset($_POST['password']) ? trim($_POST['password']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if (empty($user) || empty($email) || empty($pw)) {
        rispostaJson(false, 'Tutti i campi sono obbligatori.', [], 400);
    }


    //controllo lunghezza username
    if(strlen($user) < 3 || strlen($user) > 20){
        rispostaJson(false, 'Username deve avere dai 3 ai 20 caratteri.', [], 400);
    }

    //altri controlli username
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $user)) {
        rispostaJson(false, 'Username può contenere solo lettere, numeri e underscore.', [], 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        rispostaJson(false, 'Formato email non valido.', [], 400);
        exit();
    }

    //correttezza password
    if(strlen($pw) < 8 ){
        rispostaJson(false, 'La password deve avere almeno 8 caratteri.', [], 400);
    }

    //controllo caratteri password
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/', $pw)) {
        rispostaJson(false, 'La password deve contenere almeno una lettera maiuscola, una minuscola, un numero e un carattere speciale.', [], 400);
    }



    //inserimento dati
    try {
        $db = new Database;

        $pdo = $db->getConnection();

        //hashing della password
        $hashpw = password_hash($pw, PASSWORD_BCRYPT);
        $sql = "INSERT INTO utenti(username, email, password_hash) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user, $email, $hashpw]);

        //se arrivo qui tutto ok;
        rispostaJson(true, 'Registrazione avvenuta con successo');
    } catch (PDOException $e) {
    if ($e->getCode() == 23000) { // Codice SQL standard per violazione di vincolo di unicità
            rispostaJson(false, 'Username o email già in uso.', [], 409);
        } else {
            // Per tutti gli altri errori del database
            error_log('Errore di registrazione: ' . $e->getMessage());
           rispostaJson(false, 'Si è verificato un errore durante la registrazione. Riprova più tardi.', [], 500);
        }
    }
} elseif ($action === 'login') {
    //recupero i dati e li sanitizzo
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $pw = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($email) || empty($pw)) {
        rispostaJson(false, 'Email e password sono obbligatori.', [], 400);
    }

    try {
        $db = new Database;
        $pdo = $db->getConnection();

        $sql = "SELECT * FROM utenti WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($pw, $user['password_hash'])) {
            rispostaJson(false, 'Email o password errati.', [], 401);
        }

        // Login avvenuto con successo
        session_regenerate_id(true); //per maggiore sicurezza
        $_SESSION['user_id'] = $user['id_utente'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['ruolo'];
        rispostaJson(true, 'Login effettuato con successo.', [
            'user_id' => $user['id_utente'],
            'username' => $user['username'],
            'user_role' => $user['ruolo']
        ]);

        //unione del carrello con la sessione
        if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])){
            try {
                $db = new Database();
                $pdo = $db->getConnection();

                foreach($_SESSION['cart'] as $productId => $quantity){
                    //se il prodotto non è nel carrello faccio una insert,
                    //se è già presente faccio un update

                    $sql = "INSERT INTO carrelli_utente (id_utente, id_prodotto, quantita)
                            VALUES (:id_utente, :id_prodotto, :quantita)
                            ON DUPLICATE KEY UPDATE quantita = quantita + VALUES(quantita)";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':id_utente' => $_SESSION['user_id'],
                        ':id_prodotto' => $productId,
                        ':quantita' => $quantity
                    ]);
                }
                //svuoto il carrello della sessione
                unset($_SESSION['cart']);
            } catch ( Exception $e) {
                error_log('Errore durante unione carrello per utente:' .$_SESSION['user_id'] . ': ' . $e->getMessage());
            }
        }

    } catch (PDOException $e) {
        error_log('Errore di login: ' . $e->getMessage());
        rispostaJson(false, 'Si è verificato un errore durante il login. Riprova più tardi.', [], 500);
    } 
} else {
    rispostaJson(false, 'Azione non valida.', [], 400);
}
?>