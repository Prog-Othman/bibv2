<?php
require_once 'includes/header.php';
require_once '../config/Database.php';

// Get user's notifications
$stmt = $pdo->prepare("SELECT * FROM n_notifications WHERE id_utilisateur = ? ORDER BY date_creation DESC");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();

// Mark notifications as read
$stmt = $pdo->prepare("UPDATE n_notifications SET est_lu = true WHERE id_utilisateur = ? AND est_lu = false");
$stmt->execute([$_SESSION['user_id']]);
?>

<div class="container mt-4">
    <h2>Notifications</h2>
    
    <?php if (empty($notifications)): ?>
        <div class="alert alert-info">
            You have no notifications.
        </div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($notifications as $notification): ?>
                <div class="list-group-item <?php echo $notification['est_lu'] ? '' : 'list-group-item-primary'; ?>">
                    <div class="d-flex w-100 justify-content-between">
                        <p class="mb-1"><?php echo htmlspecialchars($notification['message']); ?></p>
                        <small><?php echo date('M j, Y g:i A', strtotime($notification['date_creation'])); ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?> 