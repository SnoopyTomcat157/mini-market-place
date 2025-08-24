<?php
session_start();
require_once 'src/core/functions.php';
require_once 'src/core/Database.php';

assicuraUtenteAutenticato();

$user = null;
$errorMessage = null;


try{
    $database = new Database();
    $pdo = $database->getConnection();

    $sql = "SELECT username, email, nome, cognome, indirizzo, citta, cap, provincia
            FROM utenti
            WHERE id_utente = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$user){
        throw new Exception("Utente non trovato");
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    $errorMessage = "Impossibile caricare i dati del tuo profilo. Per favore riprova più tardi.";
}

$pageTitle = "Modifica Profilo";
require_once 'src/templates/header.php';
?>

<section class="form-container">
    <h1>Modifica il tuo Profilo</h1>
    <p>Aggiorna le tue informazioni personali</p>

    <?php if($errorMessage): ?>
        <p class="feedback-message error"><?php echo $errorMessage; ?></p>
    <?php elseif($user): ?>
        <form id="editProfileForm">
            <h3>Informazioni Account</h3>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>">
            </div>

            <hr>
            <h3>Indirizzo di Spedizione</h3>
            <div class="form-group">
                <label for="indirizzo">Indirizzo</label>
                <input type="text" name="indirizzo" id="indirizzo" value="<?php echo htmlspecialchars($user['indirizzo']); ?>">
            </div>

            <div class="form-group">
                <label for="citta">Città</label>
                <input type="text" name="citta" id="citta" value="<?php echo htmlspecialchars($user['citta']); ?>">
            </div>

            <div class="form-group">
                <label for="cap">CAP</label>
                <input type="text" name="cap" id="cap" value="<?php echo htmlspecialchars($user['cap']); ?>">
            </div>

            <div class="form-group">
                <label for="provincia">Provincia</label>
                <input type="text" name="provincia" id="provincia" value="<?php echo htmlspecialchars($user['provincia']); ?>">
            </div>

            <hr>
            <p style ="text-align: center; margin-bottom: 20px">Lascia i sottostanti campi vuoti se non vuoi modificare la password.</p>

            <div class="form-group">
                <label for="password">Nuova Password</label>
                <input type="password" name="password" id="password" placeholder="Inserisci la nuova password">
            </div>

            <div class="form-group">
                <label for="confirm_password">Conferma Nuova Password</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Conferma la nuova password">
            </div>
            <button type="submit" class="button-primary">Salva Modifiche</button>
        </form>
        <div id="feedbackMessage" class="feedback-message"></div>
    <?php endif; ?>
</section>

<script src="js/utilits.js"></script>
<script src="js/profile.js"></script>

<?php 
require_once 'src/templates/footer.php';
?>