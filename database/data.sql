-- Questo script inserisce i dati iniziali, come l'utente amministratore.
-- Va eseguito DOPO aver creato le tabelle con schema.sql.


INSERT INTO `utenti` (`username`, `email`, `password_hash`, `ruolo`) VALUES
('admin', 'admin@example.com', '$2y$12$iP1YtXIzSD8ApYokUCYLG.y0EUQcneP0MkCF1iN69i5YAzlH2uTy.', 'admin');

-- password per MarioRossi e AnnaVerdi: password123
INSERT INTO `utenti` (`username`, `email`, `password_hash`, `ruolo`) VALUES
('MarioRossi', 'mario.rossi@example.com', '$2y$10$gL3gS.jN9G.jL4hK.jH5g.uR2i.jL4hK.jH5g.uR2i.jL4hK', 'venditore'),
('AnnaVerdi', 'anna.verdi@example.com', '$2y$10$dK8k.jH5g.uR2i.jL4hK.jH5g.uR2i.jL4hK.jH5g.uR2i.jL', 'acquirente');

-- -----------------------
-- CATEGORIE DI ESEMPIO
-- -----------------------

INSERT INTO `categorie` (`id_categoria`, `id_categoria_padre`, `nome_categoria`) VALUES
(1, NULL, 'Elettronica'),
(2, NULL, 'Abbigliamento'),
(3, NULL, 'Casa e Cucina'),
(4, 1, 'Smartphone'),
(5, 1, 'Laptop'),
(6, 2, 'T-shirt'),
(7, 3, 'Elettrodomestici');


-- -------------------------
-- PRODOTTI DI ESEMPIO
-- -------------------------
-- Tutti i prodotti sono associati all'utente venditore con id_utente = 2
INSERT INTO `prodotti` (`id_prodotto`, `id_utente_venditore`, `id_categoria`, `nome_prodotto`, `descrizione_prodotto`, `prezzo`, `quantita_disponibile`, `nome_file_immagine`, `stato_prodotto`) VALUES
(1, 2, 4, 'Smartphone Ultra X', 'L''ultimo modello con fotocamera da 200MP e display super fluido. Perfetto per la fotografia e il gaming.', 799.99, 50, 'smartphone_ultra_x.jpg', 'disponibile'),
(2, 2, 5, 'Laptop ProBook 15', 'Un laptop potente e leggero, ideale per professionisti e studenti. Processore i7, 16GB di RAM e SSD da 512GB.', 1250.00, 30, 'laptop_probook_15.jpg', 'disponibile'),
(3, 2, 6, 'T-shirt Cotone Bio', 'Una T-shirt classica realizzata in 100% cotone biologico. Morbida, comoda e sostenibile.', 29.90, 150, 'tshirt_cotone_bio.jpg', 'disponibile'),
(4, 2, 7, 'Macchina da Caffè Express', 'Prepara un caffè espresso come al bar. Facile da usare e da pulire.', 149.50, 80, 'macchina_caffe_express.jpg', 'disponibile'),
(5, 2, 4, 'Smartphone Basic Phone', 'Un telefono semplice e affidabile, con una batteria che dura due giorni. Ideale come secondo telefono.', 120.00, 200, 'smartphone_basic_phone.jpg', 'disponibile');

