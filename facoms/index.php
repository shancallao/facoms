<?php
require_once __DIR__ . '/app/controllers/AuthController.php';

$auth = new AuthController();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Faculty Companion Login</title>
    <style>
        body { font-family: Arial; display:flex; justify-content:center; align-items:center; height:100vh; background:#f4f4f4; }
        form { background:white; padding:30px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1); width:300px; }
        input { width:100%; padding:10px; margin:8px 0; border:1px solid #ccc; border-radius:5px; }
        button { background:#007bff; color:white; padding:10px; border:none; border-radius:5px; cursor:pointer; width:100%; }
        button:hover { background:#0056b3; }
    </style>
</head>
<body>
    <form method="POST" action="">
        <h2>Login</h2>
        <input type="text" name="username" placeholder="Username" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit">Login</button>
        <?php $auth->login(); ?>
    </form>
</body>
</html>
