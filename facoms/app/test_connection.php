<?php
require_once 'config.php';

$db = new Database();
$conn = $db->getConnection();

if ($conn) {
    echo "<h3 style='color: green;'>✅ Database connected successfully!</h3>";
} else {
    echo "<h3 style='color: red;'>❌ Database connection failed!</h3>";
}
?>
