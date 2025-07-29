<?php
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