<?php
require_once __DIR__ . './config.php'; 

class Database {
    private static $instance = null;
    
    // Méthode pour obtenir l'instance de la connexion et la rendre globale
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        
        return self::$instance;
    }

    // Méthode pour se connecter à la base de données et rendre $connexion globale
    private function __construct() {
        global $connexion; // Déclare la variable $connexion comme globale
        try {
            // Crée la connexion PDO et l'affecte à la variable globale $connexion
            $connexion = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
            $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connexion échouée : " . $e->getMessage());
        }
    }

    // Empêcher la duplication de l'instance
    private function __clone() {}

    // Empêcher la désérialisation
    private function __wakeup() {}
}
?>
