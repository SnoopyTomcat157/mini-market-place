-- File: database/data.sql
-- Questo script inserisce i dati iniziali, come l'utente amministratore.
-- Va eseguito DOPO aver creato le tabelle con schema.sql.

INSERT INTO `utenti` (`username`, `email`, `password_hash`, `ruolo`) VALUES
('admin', 'admin@example.com', '$2y$12$iP1YtXIzSD8ApYokUCYLG.y0EUQcneP0MkCF1iN69i5YAzlH2uTy.', 'admin');