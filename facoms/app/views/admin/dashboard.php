<?php
session_start();

// Only allow Super Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: /facoms/");
    exit;
}

require_once __DIR__ . '/../../../config.php';
$db = new Database();
$conn = $db->getConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = 'department_head';

    // Insert new DH account
    $stmt = $conn->prepare("
        INSERT INTO users (first_name, last_name, email, username, password, role, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$first_name, $last_name, $email, $username, $password, $role]);

    echo "<p style='color:green;'>✅ Department Head account created successfully!</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Super Admin Dashboard</title>
</head>
<body>
  <h2>Super Admin Dashboard</h2>
  <p>Welcome, <?php echo $_SESSION['username']; ?>!</p>

  <h3>Create Department Head Account</h3>
  <form method="POST">
      <label>First Name:</label><br>
      <input type="text" name="first_name" required><br><br>

      <label>Last Name:</label><br>
      <input type="text" name="last_name" required><br><br>

      <label>Email:</label><br>
      <input type="email" name="email" required><br><br>

      <label>Username:</label><br>
      <input type="text" name="username" required><br><br>

      <label>Password:</label><br>
      <input type="text" name="password" required><br><br>

      <button type="submit">Create DH Account</button>
  </form>
</body>
</html>
