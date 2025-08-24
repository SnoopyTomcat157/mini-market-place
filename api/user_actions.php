<?php
session_start();
require_once '../src/core/functions.php';
require_once '../src/core/Database.php';

if (!isset($_SESSION['user_id'])) {
    rispostaJson(false, 'Devi essere loggato per eseguire questa azione.', [], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    rispostaJson(false, 'Metodo non consentito.', [], 405);
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$db = new Database();
$pdo = $db->getConnection();
$userId = $_SESSION['user_id'];

switch ($action) {
    case 'update_profile':
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $indirizzo = isset($_POST['indirizzo']) ? trim($_POST['indirizzo']) : '';
        $citta = isset($_POST['citta']) ? trim($_POST['citta']) : '';
        $cap = isset($_POST['cap']) ? trim($_POST['cap']) : '';
        $provincia = isset($_POST['provincia']) ? trim($_POST['provincia']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        if (empty($username) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            rispostaJson(false, 'Username ed email sono obbligatori e l\'email deve essere valida.', [], 400);
        }
        if (strlen($username) < 3 || strlen($username) > 20) {
            rispostaJson(false, 'L\'username deve avere tra 3 e 20 caratteri.', [], 400);
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            rispostaJson(false, 'L\'username può contenere solo lettere, numeri e underscore.', [], 400);
        }

        $fields = [
            'username = ?', 'email = ?', 'indirizzo = ?',
            'citta = ?', 'cap = ?', 'provincia = ?'
        ];
        $params = [$username, $email, $indirizzo, $citta, $cap, $provincia];

        //aggiorna la password solo se ne è stata fornita una nuova
        if (!empty($password)) {
            if (strlen($password) < 8) {
                rispostaJson(false, 'La nuova password deve essere di almeno 8 caratteri.', [], 400);
            }
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/', $password)) {
                rispostaJson(false, 'La nuova password deve contenere almeno una lettera maiuscola, una minuscola, un numero e un carattere speciale.', [], 400);
            }
            
            $fields[] = 'password_hash = ?';
            $params[] = password_hash($password, PASSWORD_BCRYPT);
        }

        $sql = "UPDATE utenti SET " . implode(', ', $fields) . " WHERE id_utente = ?";
        $params[] = $userId;

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            //aggiorna i dati nella sessione se sono cambiati
            $_SESSION['username'] = $username;

            rispostaJson(true, 'Profilo aggiornato con successo!');
        } catch (PDOException $e) {
            error_log("Errore aggiornamento profilo: " . $e->getMessage());
            if ($e->getCode() == 23000) {
                rispostaJson(false, 'Username o email già in uso da un altro account.', [], 409);
            } else {
                rispostaJson(false, 'Si è verificato un errore durante l\'aggiornamento.', [], 500);
            }
        }
        break;

    case 'become_seller':
        try {
            $sql = "UPDATE utenti SET ruolo = 'venditore' WHERE id_utente = ? AND ruolo = 'acquirente'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);

            if ($stmt->rowCount() > 0) {
                //se l'aggiornamento ha successo, aggiorno anche la sessione
                $_SESSION['user_role'] = 'venditore';
                rispostaJson(true, 'Congratulazioni! Il tuo account è stato aggiornato a Venditore.');
            } else {
                rispostaJson(false, 'Impossibile aggiornare il ruolo. Potresti essere già un venditore.', [], 400);
            }
        } catch (PDOException $e) {
            error_log("Errore upgrade a venditore: " . $e->getMessage());
            rispostaJson(false, 'Si è verificato un errore durante l\'aggiornamento del tuo account.', [], 500);
        }
        break;

    default:
        rispostaJson(false, 'Azione non valida.', [], 400);
        break;
}
?>
