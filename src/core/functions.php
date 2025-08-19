<?php

/**
 * Invia una risposta standard in formato JSON e termina lo script.
 * @param bool $successo - Lo stato della richiesta (true o false).
 * @param string $messaggio - Un messaggio per il frontend.
 * @param array $dati - Dati aggiuntivi da inviare.
 * @param int $codiceHttp - Il codice di stato HTTP da inviare.
 */
function rispostaJson($successo, $messaggio, $dati = [], $codiceHttp = 200) {
    header('Content-Type: application/json');
    http_response_code($codiceHttp);
    echo json_encode([
        'success' => $successo,
        'message' => $messaggio,
        'data' => $dati
    ]);
    exit();
}

/**
 * Controlla se l'utente è loggato. Se non lo è, lo reindirizza al login.
 * Da usare all'inizio delle pagine protette.
 */
function assicuraUtenteAutenticato() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Controlla se l'utente ha uno dei ruoli permessi. Se non li ha, lo reindirizza.
 * @param array $ruoliPermessi - Un array di ruoli permessi (es. ['admin', 'venditore']).
 */
function assicuraUtenteConRuolo($ruoliPermessi) {
    assicuraUtenteAutenticato(); // Prima di tutto, deve essere loggato
    if (!in_array($_SESSION['user_role'], $ruoliPermessi)) {
        header('Location: error_403.php'); // Pagina di Accesso Negato
        exit();
    }
}

//file per l'ottimizzazione delle immagini

function optimizeImage($sourcePath, $destinationPath, $maxwidth = 800, $jpegQuality = 500, $pngCompression = 6){
    $imageInfo = getimagesize($sourcePath);
    if(!$imageInfo)
    return false;

    list($width, $height, $imageType) = $imageInfo;

    switch($imageInfo){
        case IMAGETYPE_JPEG:
            $sourceImage = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $sourceImage = imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_GIF:
            $sourceImage = imagecreatefromgif($sourcePath);
            break;

        default:
            return false;
    }

    if($width > $maxwidth){
        $ratio = $maxwidth / $width;
        $newWidth = $maxwidth;
        $newHeight = $height * $ratio;
    } else {
        $newWidth = $width;
        $newHeight = $height;
    }

    //creo una nuova immagine con le niuove dimensioni
    $image = imagecreatetruecolor($newWidth, $newHeight);

    //trasparenza per PNG e GIF
    if($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
        imagefilledrectangle($image, 0, 0, $newWidth, $newHeight, $transparent);
    }

    //copio l'immagine in quella appena creata
    imagecopyresampled($image, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    $ok = false;
    switch($imageType) {
        case IMAGETYPE_JPEG:
            $ok = imagejpeg($image, $destinationPath, $jpegQuality);
            break;
        case IMAGETYPE_PNG:
            $ok = imagepng($image, $destinationPath, $pngCompression);
            break;
        case IMAGETYPE_GIF:
            $ok = imagegif($image, $destinationPath);
            break;
    }

    imagedestroy($sourceImage);
    imagedestroy($image);

    return $ok;
}

?>