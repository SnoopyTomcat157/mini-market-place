<?php

require_once __DIR__ .'/../../config/config.php';


//classe per gestire le connessioni al database
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $charset = DB_CHARSET;

    private $conn;

    public function getConnection() {
        if($this->conn){
            return $this->conn;
        }

        $dsn = "mysql:host=" . $this->host. ";dbname=" . $this->db_name . ";charset=" .$this->charset;

        //gestione ottimale egli errori
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  //modalità di fetch
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            error_log ('Connection failed:' . $e->getMessage());
            throw new Exception('Database connection error');
        }
        return $this->conn;
    }
}

?>