<?php 
// bootstrap.php (in root folder)
require_once __DIR__ . '/config/config.php';  // Load config
require_once __DIR__ . '/core/Router.php';   // Load core files

// Start session, error reporting, etc.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Instantiate the router
$router = new Router();
