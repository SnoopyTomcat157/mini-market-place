-- File: database/data.sql
-- Questo script inserisce i dati iniziali, come l'utente amministratore.
-- Va eseguito DOPO aver creato le tabelle con schema.sql.

INSERT INTO `utenti` (`username`, `email`, `password_hash`, `ruolo`) VALUES
('admin', 'admin@example.com', '$2y$10$vV4JsOSBSjInvqKHOaRyyenCaw4IHKJpQmBqXMqCcn3tZ2pwny5Rmutenti', 'admin');