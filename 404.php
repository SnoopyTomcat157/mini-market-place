<?php
http_response_code(404);
$pageTitle = "404 Not Found";
require_once 'src/templates/header.php';
?>

<link rel="stylesheet" href="css/error.css">

<section class="error-container">
    <div class="error-code">404</div>
    <h1>Pagina Non Trovata</h1>
    <p>Oops! Sembra che la pagina che stai cercando non esista, o sia stata spostata.</p>
    <a href="index.php">Torna alla Homepage</a>
</section>

<?php
require_once 'src/templates/footer.php';
?>