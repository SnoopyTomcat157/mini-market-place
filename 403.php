<?php
http_response_code(403);
$pageTitle = "Accesso Negato (403)";
require_once 'src/templates/header.php';
?>

<link rel="stylesheet" href="css/error.css">

<section class="error-container">
    <div class="error-code">403</div>
    <h1>Accesso Negato</h1>
    <p>Non hai i permessi necessari per visualizzare questa pagina.</p>
    <a href="index.php" class="button-primary">Torna alla Homepage</a>
</section>

<?php
require_once 'src/templates/footer.php';
?>