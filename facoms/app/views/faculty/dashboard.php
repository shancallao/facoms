<?php
session_start();
require_once __DIR__ . '/../../models/UserModel.php';
$userModel = new UserModel();

$id = $_SESSION['id'];
$stmt = $userModel->conn->prepare("SELECT * FROM users WHERE id=:id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<h2>Welcome, <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h2>

<?php if (!empty($user['photo'])): ?>
    <img src="/facoms/public/assets/uploads/<?= htmlspecialchars($user['photo']) ?>" width="80" height="80" style="border-radius:50%;vertical-align:middle;">
<?php else: ?>
    <img src="/facoms/public/assets/uploads/default.png" width="80" height="80" style="border-radius:50%;vertical-align:middle;">
<?php endif; ?>
