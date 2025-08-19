<?php
    session_start();

    //controllo che utente sia loggato
    if(isset($_SESSION['user_id'])) {
        header('Location: user_dashboard.php');
        exit();
    }

    $pageTtile = "Login - MiniMarketplace";
    require_once 'src/templates/header.php';

    ?>
    
    <link rel="stylesheet" href="css/auth.css">
    
    <section class='form-container'>
        <h1>Accedi al tuo Account</h1>
        <p>Bentornato! Inserisci le tue credenziali per continuare.</p>


        <form id="loginForm">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" required>
                    <button type="button" class="toggle-password" title="Mostra/Nascondi password">
                        <!-- Icona occhio aperto (default) -->
                        <svg class="eye-icon eye-open" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        <!-- Icona occhio barrato (nascosta) -->
                    <svg class="eye-icon eye-closed" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="button-primary">
            <span class="button-text-default">Accedi</span>
            <div class="button-loading-content">
                <div class="loader"></div>
                <span class="button-text-loading"></span>
            </div>
        </button>
        </form>
         <div id="feedbackMessage" class="feedback-message"></div>

        <div class="form-footer">
            <p>Non hai ancora un account? <a href="register.php">Registrati qui</a>.</p>
        </div>

    </section>

    <script src="js/utils.js"></script>
    <script src="js/auth.js"></script>

    <?php
    require_once 'src/templates/footer.php';
?>