<?php
/**
 * Script temporaneo per generare un hash sicuro per la password dell'admin.
 *
 * ISTRUZIONI:
 * 1. Sostituisci 'LaMiaPasswordSuperSegreta123!' con la password che desideri.
 * 2. Salva questo file come "crea_hash.php" nella cartella principale del tuo progetto.
 * 3. Visita http://localhost/mini_marketplace/crea_hash.php nel tuo browser.
 * 4. Copia la lunga stringa di testo che appare.
 * 5. Incolla la stringa copiata nel tuo file `database/data.sql` al posto dell'hash esistente.
 * 6. CANCELLA questo file ("crea_hash.php") per sicurezza!
 */

// Scegli la password che vuoi per il tuo admin
$passwordDaCriptare = 'Admin1234';

// Opzioni per l'algoritmo BCRYPT (standard e sicuro)
$opzioni = [
    'cost' => 12, // Il costo computazionale. 12 è un buon punto di partenza.
];

// Genera l'hash usando l'algoritmo BCRYPT
$hash = password_hash($passwordDaCriptare, PASSWORD_BCRYPT, $opzioni);

// Stampa l'hash a schermo. Questo è il valore da copiare.
echo "Copia questo hash nel tuo file data.sql:<br><br>";
echo htmlspecialchars($hash);

?>
