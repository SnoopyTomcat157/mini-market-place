<div class="product-card">
    <a href="product_detail.php?id=<?php echo htmlspecialchars($product['id_prodotto']); ?>">
        <div class="product-image-container">
            <?php 
                // Usa l'immagine del prodotto o un'immagine di default se non è presente
                $image_name = isset($product['nome_file_immagine']) && !empty($product['nome_file_immagine']) 
                                ? $product['nome_file_immagine'] 
                                : 'default_image.png'; 
            ?>
            <img src="uploads/products/<?php echo htmlspecialchars($image_name); ?>" alt="<?php echo htmlspecialchars($product['nome_prodotto']); ?>">
        </div>
        <div class="product-info">
            <h3><?php echo htmlspecialchars($product['nome_prodotto']); ?></h3>
            <p class="price"><?php echo htmlspecialchars(number_format($product['prezzo'], 2, ',', '.')); ?> €</p>
        </div>
    </a>
</div>
