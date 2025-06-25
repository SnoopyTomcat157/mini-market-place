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
    
<header class="main-header">
    <a href="index.php" class="logo">MiniMarketplace</a>
    
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
                <!-- dropdown per l'utente loggato -->
                <li class="dropdown">
                    <a href="user_dashboard.php">Il Mio Account</a>
                    <ul class="dropdown-menu">
                        <li><a href="user_dashboard.php">Dashboard</a></li>
                        <li><a href="order_history.php">I Miei Ordini</a></li>
                        <li><a href="edit_profile.php">Modifica Profilo</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="seller_dashboard.php">Area Venditore</a>
                     <ul class="dropdown-menu">
                        <li><a href="seller_dashboard.php">I Miei Prodotti</a></li>
                        <li><a href="add_product.php">Aggiungi Prodotto</a></li>
                    </ul>
                </li>
                <li><a href="api/auth.php?action=logout">Logout</a></li>
            <?php else: ?>
                <!-- utente non loggato -->
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
</header>

<main class="container">