<?php
if (!isset($pdo)) {
    // Inizializza la connessione al database se non è già stata creata
    require_once __DIR__ . '/../../src/core/Database.php';
    try {
        $database = new Database();
        $pdo = $database->getConnection();
    } catch (Exception $e) {
        $pdo = null;
        error_log('Errore connessione DB nell\'header: ' . $e->getMessage());
    }
}
//logica prodotti cercati
$categories = [];
if ($pdo) {
    try {
        $sql_categories = "SELECT id_categoria, nome_categoria FROM categorie ORDER BY nome_categoria ASC";
        $stmt_categories = $pdo->query($sql_categories);
        $categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Errore recupero categorie per header: ' . $e->getMessage());
    }
}

$currentPage = basename($_SERVER['SCRIPT_NAME']);
$pages = ['login.php', 'register.php'];
$isPages = in_array($currentPage, $pages);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'MiniMarketplace'; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel ="stylesheet" href ="css/form.css">

</head>
<body>

<header class="main-header <?php if ($isPages) echo 'header-simple'; ?>">
    <a href="index.php" class="logo"><img src="images/SVG/logo.svg" alt="MiniMarketplace"></a>
    <?php
        // Mostra la barra di ricerca e la navbar solo se non siamo nelle pagine di login o registrazione
        if(!$isPages) :
    ?>
    <div class="search-container-header">
        <form id="searchForm">
            <div class="search-bar">
                <select name="category" id="categorySelect">
                    <option value="">Tutte le categorie</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['id_categoria']); ?>">
                            <?php echo htmlspecialchars($category['nome_categoria']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="text" id="searchInput" name="query" placeholder="Cosa stai cercando?">
                <button type="submit" class="search-button">Cerca</button>
            </div>
        </form>
    </div>
    
    <!-- menu mobile -->
    <button id="mobile-menu-toggle" class="mobile-menu-toggle">
        <svg class="hamburger-icon" viewBox="0 0 100 80" width="25" height="25">
            <rect width="100" height="15" rx="8"></rect>
            <rect y="30" width="100" height="15" rx="8"></rect>
            <rect y="60" width="100" height="15" rx="8"></rect>
        </svg>
    </button>
    
    <nav class="main-nav">
        <ul>
            <li><a href="index.php">Home</a></li>
            
            <?php if (isset($_SESSION['user_id'])): ?>
            <!-- menu a tendina per tutti gli utenti loggati -->
            <li class="dropdown">
                <a href="user_dashboard.php">Il Mio Account</a>
                <ul class="dropdown-menu">
                    <li><a href="user_dashboard.php">Dashboard</a></li>
                    <li><a href="order_history.php">I Miei Ordini</a></li>
                    <li><a href="edit_profile.php">Modifica Profilo</a></li>
                </ul>
            </li>
            
            <!-- menu a tendina specifico per i venditori -->
            <?php if ($_SESSION['user_role'] === 'venditore'): ?>
                <li class="dropdown">
                    <a href="seller_dashboard.php">Area Venditore</a>
                    <ul class="dropdown-menu">
                        <li><a href="seller_dashboard.php">I Miei Prodotti</a></li>
                        <li><a href="add_product.php">Aggiungi Prodotto</a></li>
                    </ul>
                </li>
            <?php endif; ?>

            <!-- link per admin -->
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <li><a href="admin_dashboard.php">Pannello Admin</a></li>
            <?php endif; ?>

            <li><a href="api/auth.php?action=logout">Logout</a></li>
        <?php else: ?>
            <!-- link per utenti non loggati -->
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Registrati</a></li>
        <?php endif; ?>


            <li>
                <a href="cart_page.php" class="cart-link">
                    <!-- Icona del carrello (SVG) -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="cart-icon">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <!-- Contatore articoli -->
                    <span id="cart-item-count"></span> 
                </a>
            </li>
        </ul>
    </nav>
    <?php 
        endif;
    ?>
</header>

<main class="container">