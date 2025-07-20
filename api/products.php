<?php

    require_once '../config/config.php';
    require_once '../src/core/Database.php';

    header('Content-Type: application/json');

    if(!isset($_SERVER['user_id'])){
        http_response_code(401);
        echo json_encode(['succes' => false, 'message' => 'Devi aver effettuato il login per eseguire questa azione']);
        exit();
    }

    //se l'utente non ha almeno ruolo venditore non può aggiungere/togliere prodotti

    if(!$_SESSION['user_role'] !== 'venditore' && $_SESSION['user_role'] !== 'admin'){
        http_response_code(403);
        echo json_encode(['succes' => false, 'message' => 'Non hai i permessi per eseguire questa azione']);
        exit();
    }
    
    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        http_response_code(405);
        echo json_encode(['succes' => false, 'message' => 'Metodo non consentito']);
        exit();
    }


    $action = isset(($_POST['action'])) ? $_POST['action'] : '';
    $database = new Database;
    
    $pdo = $database->getConnection();
    switch($action){
        case 'create':
            //creazione prodotto
            $nome_prodotto = isset($_POST['nome_prodotto']) ? trim($_POST['nome_prodotto']) : '';
            $descrizione = isset($_POST['descrizione']) ? trim($_POST['descrizione']) : '';
            $prezzo = isset($_POST['prezzo']) ? filter_var($_POST['prezzo'], FILTER_VALIDATE_FLOAT) : 0.0;
            $categoria_id = isset($_POST[$categoria_id]) ? (int)$_POST['categoria_id'] : 0;
            $quantita = isset($_POST['quantita']) ? (int)$_POST['quantita'] : 0;

            if(empty($nome_prodotto || $prezzo <= 0 || $categoria_id <= 0)){
                http_response_code(400);
                json_encode(['success' => false, 'message' => 'Nome prezzo e categoria sono obbligatori']);
                exit();
            }
            $nome_file_immagine = null;
            if(isset($_FILES['immagine']) && $_FILES['immagine']['error'] == 0) {
                $nome_file_immagine = 'placeholder_'. time(). 'jpg';

            }

            try{    
                $sql = "INSERT INTO prodotti (id_utente_venditore, id_categoria, nome_prodotto, descrizione_prodotto, prezzo, quantita_disponibile, nome_file_immagine)
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_SESSION['user_id'], $categoria_id, $nome_prodotto, $descrizione, $prezzo, $quantita, $nome_file_immagine]);

                echo json_encode(['success' => true, 'message' => 'Prodotto aggiunto con successo']);
            }
            catch(PDOException $e){
                http_response_code(500);
                error_log('Errore durante l\'aggiunta del prodotto: ' . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Si è verificato un errore durante l\'aggiunta del prodotto. Riprova più tardi.']);
            }
            break;

        case 'update':
            //aggiornamento prodotto
            $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            if($productId <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID prodotto non valido']);
                exit();
            }

            $fields = [];
            $params = [];
        
            // Aggiungi i campi solo se sono stati inviati nel form
            if (isset($_POST['nome_prodotto'])) { $fields[] = 'nome_prodotto = ?'; $params[] = trim($_POST['nome_prodotto']); }
            if (isset($_POST['descrizione'])) { $fields[] = 'descrizione_prodotto = ?'; $params[] = trim($_POST['descrizione']); }
            if (isset($_POST['prezzo'])) { $fields[] = 'prezzo = ?'; $params[] = filter_var($_POST['prezzo'], FILTER_VALIDATE_FLOAT); }
            if (isset($_POST['categoria_id'])) { $fields[] = 'id_categoria = ?'; $params[] = (int)$_POST['categoria_id']; }
            if (isset($_POST['quantita'])) { $fields[] = 'quantita_disponibile = ?'; $params[] = (int)$_POST['quantita']; }
            
            //gestione nuova immagine
            if (isset($_FILES['immagine']) && $_FILES['immagine']['error'] == 0) {
                // NOTA: Qui andrà la logica di ottimizzazione e di eliminazione della vecchia immagine.
                $nome_file_immagine = 'placeholder_update_'. time() . '.jpg';
                $fields[] = 'nome_file_immagine = ?'; 
                $params[] = $nome_file_immagine;
            }

        if (empty($fields)) {
            echo json_encode(['success' => true, 'message' => 'Nessun dato da aggiornare.']);
            exit();
        }

        $sql = "UPDATE prodotti SET " . implode(', ', $fields) . " WHERE id_prodotto = ?";
        $params[] = $productId;

        //controllo di proprietà per i venditori
        if ($_SESSION['user_role'] === 'venditore') {
            $sql .= " AND id_utente_venditore = ?";
            $params[] = $_SESSION['user_id'];
        }

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            //controlla se la riga è stata effettivamente modificata
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Prodotto aggiornato con successo!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Nessuna modifica effettuata. Il prodotto potrebbe non esistere o non hai i permessi.']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            error_log('Errore aggiornamento prodotto: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Si è verificato un errore durante l\'aggiornamento.']);
        }
        break;

        case 'delete':
            // eliminazione prodotto
            $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            if ($productId <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID prodotto non valido.']);
                exit();
            }

            try {
            
                $image_to_delete = null;
                $stmt_select = $pdo->prepare("SELECT nome_file_immagine FROM prodotti WHERE id_prodotto = ?");
                $stmt_select->execute([$productId]);
                $image_to_delete = $stmt_select->fetchColumn();

                $sql = "DELETE FROM prodotti WHERE id_prodotto = ?";
                $params = [$productId];

                // controllo di proprietà per i venditori
                if ($_SESSION['user_role'] === 'venditore') {
                    $sql .= " AND id_utente_venditore = ?";
                    $params[] = $_SESSION['user_id'];
                }

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                if ($stmt->rowCount() > 0) {
            
                    if ($image_to_delete) {
                        $filePath = '../uploads/products/' . $image_to_delete;
                        if (file_exists($filePath)) {
                            unlink($filePath); // Funzione PHP per eliminare un file
                        }
                    }
                    echo json_encode(['success' => true, 'message' => 'Prodotto eliminato con successo!']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Impossibile eliminare il prodotto. Potrebbe non esistere o non hai i permessi.']);
                }
            } catch (PDOException $e) {
                http_response_code(500);
                error_log('Errore eliminazione prodotto: ' . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Si è verificato un errore durante l\'eliminazione.']);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Azione non valida.']);
            break;
    }

?>