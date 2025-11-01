<?php
require_once __DIR__ . '/../../controllers/ManageFacultyController.php';

$controller = new ManageFacultyController();
$controller->handleRequest();
$data = $controller->getViewData();

$faculty = $data['faculty'];
$flash = $data['flash'];
$search = $data['search'];
$highlightId = isset($_GET['highlight']) ? (int)$_GET['highlight'] : null;

$defaultAvatarSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120"><rect width="120" height="120" rx="60" fill="#0f4cbd"/><text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" font-family="Segoe UI, Arial" font-size="42" fill="#ffffff">FC</text></svg>';
$defaultAvatar = 'data:image/svg+xml;utf8,' . rawurlencode($defaultAvatarSvg);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Faculty</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary: #0a3a7f;
            --secondary: #f3b904;
            --accent: #0f4cbd;
            --background: #f4f6fb;
            --card: #ffffff;
            --text-dark: #233041;
            --text-light: #6d7a8c;
            --danger: #cc2e43;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 25px;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: var(--background);
            color: var(--text-dark);
        }

        h1 {
            margin-top: 0;
            font-size: 1.8rem;
            color: var(--primary);
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-primary {
            background: var(--accent);
            color: #fff;
        }

        .btn-secondary {
            background: #e7ecfb;
            color: var(--accent);
        }

        .btn-danger {
            background: var(--danger);
            color: #fff;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(15, 76, 189, 0.18);
        }

        .card {
            background: var(--card);
            border-radius: 14px;
            box-shadow: 0 12px 26px rgba(15, 50, 86, 0.08);
            padding: 20px;
            margin-bottom: 20px;
        }

        form .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-light);
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="file"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #d8dfea;
            border-radius: 8px;
            font-size: 0.95rem;
            color: var(--text-dark);
        }

        input[type="file"] {
            padding: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f5;
            vertical-align: middle;
        }

        th {
            background: #eef3ff;
            color: var(--primary);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        tr.highlight {
            background: #fff9d7;
            animation: pulse 1.5s ease-in-out 2;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 rgba(243, 185, 4, 0.2); }
            50% { box-shadow: 0 0 18px rgba(243, 185, 4, 0.55); }
            100% { box-shadow: 0 0 0 rgba(243, 185, 4, 0.2); }
        }

        .avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(10, 58, 127, 0.2);
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .badge-request {
            display: inline-block;
            background: var(--secondary);
            color: #1b1b1b;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .flash {
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 15px;
        }

        .flash.success {
            background: #e6f7ee;
            color: #0c6b3f;
            border: 1px solid #b1e5c8;
        }

        .flash.error {
            background: #ffe9ec;
            color: #a32036;
            border: 1px solid #f5c1ca;
        }

        .search-box {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .search-box input {
            width: 220px;
        }

        .modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(10, 26, 58, 0.55);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-backdrop.active {
            display: flex;
        }

        .modal {
            background: #fff;
            border-radius: 16px;
            max-width: 480px;
            width: 100%;
            padding: 24px;
            box-shadow: 0 18px 38px rgba(15, 50, 86, 0.25);
            position: relative;
        }

        .modal h2 {
            margin-top: 0;
            margin-bottom: 18px;
        }

        .modal .close-btn {
            position: absolute;
            top: 12px;
            right: 16px;
            background: none;
            border: none;
            font-size: 1.4rem;
            cursor: pointer;
            color: var(--text-light);
        }

        .empty-state {
            text-align: center;
            padding: 30px;
            color: var(--text-light);
        }

        @media (max-width: 768px) {
            body {
                padding: 18px;
            }
            .toolbar {
                flex-direction: column;
                align-items: flex-start;
            }
            .search-box input {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <h1>Manage Faculty</h1>
        <div class="toolbar-actions">
            <a class="btn btn-secondary" href="dashboard.php">⬅ Back to Dashboard</a>
        </div>
    </div>

    <?php foreach ($flash['success'] as $message): ?>
        <div class="flash success">✅ <?= htmlspecialchars($message); ?></div>
    <?php endforeach; ?>

    <?php foreach ($flash['errors'] as $message): ?>
        <div class="flash error">⚠️ <?= htmlspecialchars($message); ?></div>
    <?php endforeach; ?>

    <section class="card">
        <form method="get" class="search-box">
            <label for="search">Search Faculty:</label>
            <input type="text" id="search" name="search" value="<?= htmlspecialchars($search ?? ''); ?>" placeholder="Name, username, email...">
            <button class="btn btn-secondary" type="submit">Search</button>
            <a class="btn btn-secondary" href="manage_faculty.php">Clear</a>
        </form>
    </section>

    <section class="card">
        <h2>Create New Faculty</h2>
        <form method="post" enctype="multipart/form-data" onsubmit="return confirmCreate(this)">
            <input type="hidden" name="action" value="create">
            <div class="form-grid">
                <div>
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div>
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
                <div>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div>
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div>
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div>
                    <label for="photo">Profile Photo (JPG/PNG)</label>
                    <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png">
                </div>
            </div>
            <div style="margin-top: 16px; display:flex; justify-content:flex-end;">
                <button class="btn btn-primary" type="submit">Create Faculty</button>
            </div>
        </form>
    </section>

    <section class="card">
        <h2>Faculty Directory</h2>
        <?php if ($faculty): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Username</th>
                            <th>Password Request</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($faculty as $row): ?>
                            <?php
                                $photo = $row['photo'] ? '/facoms/app/public/assets/uploads/' . htmlspecialchars($row['photo']) : $defaultAvatar;
                                $highlightClass = ($highlightId && $highlightId === (int)$row['id']) ? 'highlight' : '';
                            ?>
                            <tr class="<?= $highlightClass; ?>">
                                <td><img class="avatar" src="<?= $photo; ?>" alt="<?= htmlspecialchars($row['first_name']); ?>" onerror="this.onerror=null;this.src='<?= $defaultAvatar ?>';"></td>
                                <td>
                                    <strong><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></strong><br>
                                    <span class="meta">Joined <?= date('M d, Y', strtotime($row['created_at'])); ?></span>
                                </td>
                                <td><?= htmlspecialchars($row['email']); ?></td>
                                <td><?= htmlspecialchars($row['username']); ?></td>
                                <td>
                                    <?php if ($row['password_request']): ?>
                                        <span class="badge-request">Requested</span>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="actions">
                                        <button class="btn btn-secondary" type="button" onclick='openEditModal(<?= json_encode($row); ?>)'>Edit</button>
                                        <button class="btn btn-primary" type="button" onclick='openResetModal(<?= json_encode($row); ?>)'>Reset Password</button>
                                        <form method="post" onsubmit="return confirmDelete(this)">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="faculty_id" value="<?= $row['id']; ?>">
                                            <button class="btn btn-danger" type="submit">Remove</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">No faculty found. Create the first faculty profile using the form above.</div>
        <?php endif; ?>
    </section>

    <!-- Edit Faculty Modal -->
    <div id="editModal" class="modal-backdrop">
        <div class="modal">
            <button class="close-btn" onclick="closeModal('editModal')">×</button>
            <h2>Edit Faculty</h2>
            <form method="post" enctype="multipart/form-data" onsubmit="return confirmEdit(this)">
                <input type="hidden" name="action" value="update">
                <input type="hidden" id="edit_faculty_id" name="faculty_id">
                <div class="form-grid">
                    <div>
                        <label for="edit_first_name">First Name</label>
                        <input type="text" id="edit_first_name" name="first_name" required>
                    </div>
                    <div>
                        <label for="edit_last_name">Last Name</label>
                        <input type="text" id="edit_last_name" name="last_name" required>
                    </div>
                    <div>
                        <label for="edit_email">Email</label>
                        <input type="email" id="edit_email" name="email" required>
                    </div>
                    <div>
                        <label for="edit_username">Username</label>
                        <input type="text" id="edit_username" name="username" required>
                    </div>
                    <div>
                        <label for="edit_photo">Update Photo</label>
                        <input type="file" id="edit_photo" name="photo" accept=".jpg,.jpeg,.png">
                    </div>
                </div>
                <div style="margin-top:16px; display:flex; justify-content:flex-end; gap:10px;">
                    <button class="btn btn-secondary" type="button" onclick="closeModal('editModal')">Cancel</button>
                    <button class="btn btn-primary" type="submit">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div id="resetModal" class="modal-backdrop">
        <div class="modal">
            <button class="close-btn" onclick="closeModal('resetModal')">×</button>
            <h2>Reset Faculty Password</h2>
            <form method="post" onsubmit="return confirmPasswordReset(this)">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" id="reset_faculty_id" name="faculty_id">
                <p id="reset_faculty_name" style="font-weight:600;"></p>
                <div>
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div style="margin-top:16px; display:flex; justify-content:flex-end; gap:10px;">
                    <button class="btn btn-secondary" type="button" onclick="closeModal('resetModal')">Cancel</button>
                    <button class="btn btn-primary" type="submit">Update Password</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function confirmCreate(form) {
            return confirm('Create this faculty account now?');
        }

        function confirmEdit(form) {
            return confirm('Save the updates for this faculty?');
        }

        function confirmDelete(form) {
            return confirm('This will remove the faculty and delete the photo. Continue?');
        }

        function confirmPasswordReset(form) {
            const password = form.querySelector('#new_password').value;
            if (!password.trim()) {
                alert('Please enter the new password first.');
                return false;
            }
            return confirm('Update password and clear request?');
        }

        function openEditModal(row) {
            document.getElementById('edit_faculty_id').value = row.id;
            document.getElementById('edit_first_name').value = row.first_name;
            document.getElementById('edit_last_name').value = row.last_name;
            document.getElementById('edit_email').value = row.email;
            document.getElementById('edit_username').value = row.username;
            openModal('editModal');
        }

        function openResetModal(row) {
            document.getElementById('reset_faculty_id').value = row.id;
            document.getElementById('reset_faculty_name').textContent = row.first_name + ' ' + row.last_name;
            document.getElementById('new_password').value = '';
            openModal('resetModal');
        }

        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }
    </script>
</body>
</html>
