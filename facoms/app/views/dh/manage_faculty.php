<?php
require_once __DIR__ . '/../../models/UserModel.php';
$userModel = new UserModel();

// Handle Add Faculty
if (isset($_POST['add_faculty'])) {
    $fname = $_POST['first_name'];
    $lname = $_POST['last_name'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = 'faculty';
    $userModel->addFaculty($fname, $lname, $email, $username, $password, $role);
    header("Location: manage_faculty.php");
    exit;
}

// Handle Update Faculty
if (isset($_POST['edit_faculty'])) {
    $id = $_POST['id'];
    $fname = $_POST['first_name'];
    $lname = $_POST['last_name'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $photo = null;

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $filename = uniqid() . '.' . $ext;
            $destination = __DIR__ . '/../../public/assets/uploads/' . $filename;
            move_uploaded_file($_FILES['photo']['tmp_name'], $destination);
            $photo = $filename;
        }
    }

    $userModel->updateFaculty($id, $fname, $lname, $email, $username, $photo);
    header("Location: manage_faculty.php");
    exit;
}

// Handle Remove Faculty
if (isset($_GET['remove'])) {
    $id = $_GET['remove'];
    $userModel->deleteFaculty($id);
    header("Location: manage_faculty.php");
    exit;
}


// Fetch all faculty
$facultyList = $userModel->getAllFaculty();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Faculty</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }
        h2 {
            background-color: #007BFF;
            color: white;
            padding: 10px;
        }
        form, table {
            background-color: white;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        input[type="text"], input[type="email"], input[type="file"], input[type="password"] {
            padding: 5px;
            margin: 2px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        button {
            padding: 5px 10px;
            margin: 2px;
            border: none;
            border-radius: 4px;
            background-color: #ffc107; /* yellow */
            cursor: pointer;
        }
        button:hover {
            background-color: #ffdb4d;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            border: 1px solid #007BFF;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #007BFF;
            color: white;
        }
        img {
            border-radius: 50%;
        }
        .edit-form {
            display: none;
            margin-top: 10px;
            padding: 10px;
            background-color: #e7f1ff;
        }

        .button{
        text-decoration: none;
        padding: 10px 20px;
        background-color: #007BFF; 
        color: white; 
        border-radius: 5px;
        font-weight: bold;
        }
    "
    </style>
    <script>
        function toggleEditForm(id) {
            var form = document.getElementById('edit-form-' + id);
            if(form.style.display === 'none' || form.style.display === '') {
                form.style.display = 'block';
            } else {
                form.style.display = 'none';
            }
        }
    </script>
</head>
<body>
<h2>Manage Faculty</h2>

<div style="margin: 20px 0;">
    <a class="button" href="dashboard.php">⬅ Back to Dashboard</a>
</div>


<!-- Add Faculty Form -->
<form method="POST">
    <input type="text" name="first_name" placeholder="First Name" required>
    <input type="text" name="last_name" placeholder="Last Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="text" name="username" placeholder="Username" required>
    <input type="text" name="password" placeholder="Password" required>
    <button type="submit" name="add_faculty">Add Faculty</button>
</form>

<!-- Faculty Table -->
<table>
    <thead>
        <tr>
            <th>Photo</th>
            <th>Name</th>
            <th>Email</th>
            <th>Username</th>
            <th>Password Request</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($facultyList as $faculty): ?>
            <tr>
                <td>
                    <?php if ($faculty['photo']): ?>
                        <img src="/facoms/public/assets/uploads/<?= $faculty['photo'] ?>" width="50">
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($faculty['first_name'] . ' ' . $faculty['last_name']) ?></td>
                <td><?= htmlspecialchars($faculty['email']) ?></td>
                <td><?= htmlspecialchars($faculty['username']) ?></td>
                <td><?= $faculty['password_request'] ? 'Requested' : '-' ?></td>
                <td>
                    <button type="button" onclick="toggleEditForm(<?= $faculty['id'] ?>)">Edit</button>
                    <a href="?remove=<?= $faculty['id'] ?>" onclick="return confirm('Remove this faculty?')">Remove</a>

                    <!-- Hidden Edit Form -->
                    <div id="edit-form-<?= $faculty['id'] ?>" class="edit-form">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?= $faculty['id'] ?>">
                            <input type="text" name="first_name" value="<?= htmlspecialchars($faculty['first_name']) ?>" required>
                            <input type="text" name="last_name" value="<?= htmlspecialchars($faculty['last_name']) ?>" required>
                            <input type="email" name="email" value="<?= htmlspecialchars($faculty['email']) ?>" required>
                            <input type="text" name="username" value="<?= htmlspecialchars($faculty['username']) ?>" required>
                            <input type="file" name="photo" accept=".jpg,.jpeg,.png">
                            <button type="submit" name="edit_faculty">Save</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</body>
</html>
