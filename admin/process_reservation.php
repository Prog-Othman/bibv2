<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $reservation_id = $_POST['reservation_id'];
    $action = $_POST['action'];
    
    if ($action === 'ready') {
        // Update reservation status
        $stmt = $pdo->prepare("UPDATE n_reservations SET status = 'ready' WHERE id_reservation = ?");
        $stmt->execute([$reservation_id]);
        
        // Get user ID and book title for notification
        $stmt = $pdo->prepare("SELECT id_utilisateur, id_livre FROM n_reservations WHERE id_reservation = ?");
        $stmt->execute([$reservation_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $user_id = $row['id_utilisateur'];
        $book_id = $row['id_livre'];
        
        // Get book title
        $stmt = $pdo->prepare("SELECT titre FROM n_livre WHERE id_livre = ?");
        $stmt->execute([$book_id]);
        $book_title = $stmt->fetchColumn();
        
        // Create notification with type
        $message = "Votre demande pour le livre '{$book_title}' a été approuvée par l'administrateur. Veuillez venir le récupérer à la bibliothèque.";
        $stmt = $pdo->prepare("INSERT INTO n_notifications (id_utilisateur, type, message) VALUES (?, 'reservation', ?)");
        $stmt->execute([$user_id, $message]);
        if (!$stmt) {
            die('Error: ' . implode(":", $pdo->errorInfo()));
        } else {
            error_log('Notification inserted for user: ' . $user_id);
        }
        
        $_SESSION['success'] = "Book marked as ready for pickup.";
    } elseif ($action === 'cancel') {
        // ... existing code ...
    }
} 