-- Creazione del database
CREATE DATABASE IF NOT EXISTS furceri_672543 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE furceri_672543;

-- Tabella degli utenti
CREATE TABLE utenti (
    id_utente INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    ruolo ENUM('acquirente', 'venditore', 'admin') NOT NULL DEFAULT 'acquirente',
    data_registrazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabella delle categorie
CREATE TABLE categorie (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria_padre INT NULL, -- Può essere NULL per le categorie di primo livello
    nome_categoria VARCHAR(100) NOT NULL,
    descrizione_categoria TEXT,
    FOREIGN KEY (id_categoria_padre) REFERENCES categorie(id_categoria) ON DELETE SET NULL
);

-- Tabella dei prodotti
CREATE TABLE prodotti (
    id_prodotto INT AUTO_INCREMENT PRIMARY KEY,
    id_utente_venditore INT NOT NULL,
    id_categoria INT NOT NULL,
    nome_prodotto VARCHAR(255) NOT NULL,
    descrizione_prodotto TEXT,
    prezzo DECIMAL(10, 2) NOT NULL,
    quantita_disponibile INT NOT NULL DEFAULT 0,
    nome_file_immagine VARCHAR(255),
    stato_prodotto VARCHAR(50) NOT NULL DEFAULT 'disponibile',
    data_inserimento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utente_venditore) REFERENCES utenti(id_utente) ON DELETE CASCADE,
    FOREIGN KEY (id_categoria) REFERENCES categorie(id_categoria) ON DELETE RESTRICT
);

-- Tabella degli ordini
CREATE TABLE ordini (
    id_ordine INT AUTO_INCREMENT PRIMARY KEY,
    id_utente_acquirente INT NOT NULL,
    data_ordine TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    importo_totale_ordine DECIMAL(10, 2) NOT NULL,
    stato_ordine VARCHAR(50) NOT NULL DEFAULT 'in elaborazione',
    indirizzo_spedizione TEXT NOT NULL,
    FOREIGN KEY (id_utente_acquirente) REFERENCES utenti(id_utente) ON DELETE CASCADE
);

-- Tabella degli articoli contenuti in un ordine
CREATE TABLE articoli_ordine (
    id_articolo_ordine INT AUTO_INCREMENT PRIMARY KEY,
    id_ordine INT NOT NULL,
    id_prodotto INT, -- Reso NULLABLE per ON DELETE SET NULL
    quantita_ordinata INT NOT NULL,
    prezzo_al_momento_acquisto DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (id_ordine) REFERENCES ordini(id_ordine) ON DELETE CASCADE,
    FOREIGN KEY (id_prodotto) REFERENCES prodotti(id_prodotto) ON DELETE SET NULL
);

-- Tabella delle recensioni
CREATE TABLE recensioni (
    id_recensione INT AUTO_INCREMENT PRIMARY KEY,
    id_prodotto INT NOT NULL,
    id_utente_recensore INT NOT NULL,
    valutazione INT NOT NULL, -- Da 1 a 5
    testo_recensione TEXT,
    data_recensione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_prodotto) REFERENCES prodotti(id_prodotto) ON DELETE CASCADE,
    FOREIGN KEY (id_utente_recensore) REFERENCES utenti(id_utente) ON DELETE CASCADE
);

-- Tabella per i carrelli persistenti degli utenti loggati
CREATE TABLE carrelli_utente (
    id_carrello INT AUTO_INCREMENT PRIMARY KEY,
    id_utente INT NOT NULL,
    id_prodotto INT NOT NULL,
    quantita INT NOT NULL DEFAULT 1,
    data_aggiunta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utente) REFERENCES utenti(id_utente) ON DELETE CASCADE,
    FOREIGN KEY (id_prodotto) REFERENCES prodotti(id_prodotto) ON DELETE CASCADE,
    UNIQUE KEY utente_prodotto_unico (id_utente, id_prodotto)
);