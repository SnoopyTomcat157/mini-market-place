<?php
session_start();
require_once 'src/core/Database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

$userOrders = [];
$errorMessage = null;

try {
    $database = new Database();
    $pdo = $database->getConnection();
    $userId = $_SESSION['user_id'];

    $sql = "SELECT o.id_ordine, o.data_ordine, o.importo_totale_ordine, o.stato_ordine,
                   ao.quantita_ordinata, ao.prezzo_al_momento_acquisto,
                   p.nome_prodotto, p.nome_file_immagine
            FROM ordini o
                JOIN articoli_ordine ao ON o.id_ordine = ao.id_ordine
                JOIN prodotti p ON ao.id_prodotto = p.id_prodotto
            WHERE o.id_utente_acquirente = :id_utente
            ORDER BY o.data_ordine DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_utente' => $userId]);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //raggruppo prodotti per ordine
    foreach ($results as $row) {
        $orderId = $row['id_ordine'];
        if(!isset($userOrders[$orderId])) {
            $userOrders[$orderId] = [
                'details' => [
                    'id' => $orderId,
                    'date' => date('d/m/Y', strtotime($row['data_ordine'])),
                    'total' => $row['importo_totale_ordine'],
                    'status' => ucfirst($row['stato_ordine'])
                ],
                'items' => []
            ];
        }
        $userOrders[$orderId]['items'][] = [
            'name' => $row['nome_prodotto'],
            'quantity' => $row['quantita_ordinata'],
            'price' => $row['prezzo_al_momento_acquisto'],
            'image' => $row['nome_file_immagine']
        ];
    }

} catch (Exception $e) {
    error_log('Errore durante il recupero degli ordini: ' . $e->getMessage());
    $errorMessage = 'Si è verificato un errore durante il recupero degli ordini. Riprova più tardi.';
}

$pageTitle = "La Tua Dashboard";
require_once 'src/templates/header.php';
?>
<link rel="stylesheet" href="css/user_dashboard.css">

<section class="dashboard-container">
    <div class="dashboard-header">
        <h1>Ciao, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    </div>

    <?php if($errorMessage):?>
        <p class="feedback-message error"><?php echo $errorMessage; ?></p>
    <?php endif; ?>

     <div class="dashboard-content">
        <h2>Storico dei Tuoi Ordini</h2>
        
        <?php if (empty($userOrders)): ?>
            <p>Non hai ancora effettuato nessun ordine. <a href="index.php">Inizia a fare shopping!</a></p>
        <?php else: ?>
            <div class="order-history">
                <?php foreach ($userOrders as $order): ?>
                    <div class="order-card">
                        <div class="order-card-header">
                            <div>
                                <strong>Ordine #<?php echo htmlspecialchars($order['details']['id']); ?></strong>
                                <small>Effettuato il: <?php echo htmlspecialchars($order['details']['date']); ?></small>
                            </div>
                            <div>
                                <span>Stato: <?php echo htmlspecialchars($order['details']['status']); ?></span>
                                <strong>Totale: <?php echo number_format($order['details']['total'], 2, ',', '.'); ?> €</strong>
                            </div>
                        </div>
                        <div class="order-card-body">
                            <h4>Prodotti in questo ordine:</h4>
                            <ul>
                                <?php foreach ($order['items'] as $item): ?>
                                    <li>
                                        <img src="uploads/products/<?php echo htmlspecialchars(isset($item['image']) ? $item['image'] : 'default_image.png'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="order-item-img">
                                        <span><?php echo htmlspecialchars($item['name']); ?> (x<?php echo htmlspecialchars($item['quantity']); ?>)</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require_once 'src/templates/footer.php'; ?>

