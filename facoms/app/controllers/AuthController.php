<?php
require_once __DIR__ . '/../models/UserModel.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function login() {
        session_start();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);

            $user = $this->userModel->login($username, $password);

            if ($user) {
                $_SESSION['id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'super_admin') {
                    header("Location: /facoms/app/views/admin/dashboard.php");
                    exit;
                } elseif ($user['role'] === 'department_head') {
                    header("Location: /facoms/app/views/dh/dashboard.php");
                    exit;
                } elseif ($user['role'] === 'faculty') {
                    header("Location: /facoms/app/views/faculty/dashboard.php");
                    exit;
                } else {
                    echo "<p style='color:red;'>Unknown role. Please contact admin.</p>";
                }
            } else {
                echo "<p style='color:red;'>Invalid username or password</p>";
            }
        }
    }
}
?>
