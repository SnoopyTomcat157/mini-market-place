<?php
require_once '../config/config.php';
require_once '../src/core/Database.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
    exit();
}

$action = isset($_POST['action']) ? trim($_POST['action']) : '';

if ($action === 'register') {
//recupero i dati e li sanitizzo
    $user = isset($_POST['username']) ? trim($_POST['username']) : '' ;
    $pw = isset($_POST['password']) ? trim($_POST['password']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if (empty($user) || empty($email) || empty($pw)) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'message' => 'Tutti i campi sono obbligatori.']);
        exit();
    }


    //controllo lunghezza username
    if(strlen($user) < 3 || strlen($user) > 20){
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Username deve avre dai 3 ai 20 caratteri.']);
        exit();
    }

    //altri controlli username
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $user)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'L\'username può contenere solo lettere, numeri e underscore (_).']);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Formato email non valido.']);
        exit();
    }

    //correttezza password
    if(strlen($pw) < 8 ){
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'La password deve avere almeno 8 caratteri.']);
        exit();
    }

    //controllo caratteri password
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/', $pw)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'La password deve contenere almeno una lettera maiuscola, una minuscola, un numero e un carattere speciale.']);
        exit();
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
        echo json_encode(['success' => true, 'message' => 'Registrazione avvenuta con successo']);
    } catch (PDOException $e) {
    if ($e->getCode() == 23000) { // Codice SQL standard per violazione di vincolo di unicità
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Username o email già esistenti.']);
        } else {
            // Per tutti gli altri errori del database
            http_response_code(500);
            error_log('Errore di registrazione: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Si è verificato un errore durante la registrazione. Riprova più tardi.']);
        }
    }
} elseif ($action === 'login') {
    //recupero i dati e li sanitizzo
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $pw = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($email) || empty($pw)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email e password sono obbligatori.']);
        exit();
    }

    try {
        $db = new Database;
        $pdo = $db->getConnection();

        $sql = "SELECT * FROM utenti WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($pw, $user['password_hash'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Email o password errati.']);
            exit();
        }

        // Login avvenuto con successo
        session_start();
        session_regenerate_id(true); //per maggiore sicurezza
        $_SESSION['user_id'] = $user['id_utente'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['ruolo'];
        echo json_encode(['success' => true, 'message' => 'Login avvenuto con successo.']);

        

    } catch (PDOException $e) {
        http_response_code(500);
        error_log('Errore di login: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Si è verificato un errore durante il login. Riprova più tardi.']);
    }
}
?>