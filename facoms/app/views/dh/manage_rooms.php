<?php
require_once __DIR__ . '/../../controllers/ManageRoomsController.php';

$controller = new ManageRoomsController();
$controller->handleRequest();
$data = $controller->getViewData();

$rooms = $data['rooms'];
$flash = $data['flash'];
$search = $data['search'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Rooms</title>
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

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 24px;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: var(--background);
            color: var(--text-dark);
        }

        h1 {
            margin: 0;
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
            padding: 10px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-primary { background: var(--accent); color: #fff; }
        .btn-secondary { background: #e7ecfb; color: var(--accent); }
        .btn-danger { background: var(--danger); color: #fff; }

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

        label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 6px;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #d8dfea;
            border-radius: 8px;
            font-size: 0.95rem;
            color: var(--text-dark);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f5;
        }

        th {
            background: #eef3ff;
            color: var(--primary);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
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
            flex-wrap: wrap;
            align-items: center;
        }

        .search-box input {
            width: 240px;
        }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
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

        .modal-backdrop.active { display: flex; }

        .modal {
            background: #fff;
            border-radius: 16px;
            max-width: 420px;
            width: 100%;
            padding: 24px;
            box-shadow: 0 18px 38px rgba(15, 50, 86, 0.25);
            position: relative;
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

        @media (max-width: 768px) {
            body { padding: 18px; }
            .toolbar { flex-direction: column; align-items: flex-start; }
            .search-box input { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <h1>Manage Rooms</h1>
        <a class="btn btn-secondary" href="dashboard.php">⬅ Back to Dashboard</a>
    </div>

    <?php foreach ($flash['success'] as $message): ?>
        <div class="flash success">✅ <?= htmlspecialchars($message); ?></div>
    <?php endforeach; ?>

    <?php foreach ($flash['errors'] as $message): ?>
        <div class="flash error">⚠️ <?= htmlspecialchars($message); ?></div>
    <?php endforeach; ?>

    <section class="card">
        <form method="get" class="search-box">
            <label for="search">Search Rooms:</label>
            <input type="text" id="search" name="search" value="<?= htmlspecialchars($search ?? ''); ?>" placeholder="Building or room number">
            <button class="btn btn-secondary" type="submit">Search</button>
            <a class="btn btn-secondary" href="manage_rooms.php">Clear</a>
        </form>
    </section>

    <section class="card">
        <h2>Add New Room</h2>
        <form method="post" onsubmit="return confirm('Add this room to the directory?');">
            <input type="hidden" name="action" value="create">
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px,1fr)); gap:15px;">
                <div>
                    <label for="building">Building</label>
                    <input type="text" id="building" name="building" required>
                </div>
                <div>
                    <label for="room_number">Room Number</label>
                    <input type="text" id="room_number" name="room_number" required>
                </div>
                <div>
                    <label for="capacity">Capacity</label>
                    <input type="number" id="capacity" name="capacity" min="0" value="0" required>
                </div>
            </div>
            <div style="margin-top:16px; display:flex; justify-content:flex-end;">
                <button class="btn btn-primary" type="submit">Add Room</button>
            </div>
        </form>
    </section>

    <section class="card">
        <h2>Rooms Directory</h2>
        <?php if ($rooms): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Room</th>
                            <th>Building</th>
                            <th>Capacity</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rooms as $room): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($room['room_number']); ?></strong></td>
                                <td><?= htmlspecialchars($room['building']); ?></td>
                                <td><?= (int)$room['capacity']; ?></td>
                                <td><?= date('M d, Y', strtotime($room['created_at'])); ?></td>
                                <td>
                                    <div class="actions">
                                        <button class="btn btn-secondary" type="button" onclick='openEditModal(<?= json_encode($room); ?>)'>Edit</button>
                                        <form method="post" onsubmit="return confirm('Delete this room? This action cannot be undone.');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="room_id" value="<?= $room['id']; ?>">
                                            <button class="btn btn-danger" type="submit">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align:center; padding:30px; color:var(--text-light);">No rooms registered yet.</div>
        <?php endif; ?>
    </section>

    <div id="editModal" class="modal-backdrop">
        <div class="modal">
            <button class="close-btn" onclick="closeModal()">×</button>
            <h2>Edit Room</h2>
            <form method="post" onsubmit="return confirm('Save these room changes?');">
                <input type="hidden" name="action" value="update">
                <input type="hidden" id="edit_room_id" name="room_id">
                <div>
                    <label for="edit_building">Building</label>
                    <input type="text" id="edit_building" name="building" required>
                </div>
                <div>
                    <label for="edit_room_number">Room Number</label>
                    <input type="text" id="edit_room_number" name="room_number" required>
                </div>
                <div>
                    <label for="edit_capacity">Capacity</label>
                    <input type="number" id="edit_capacity" name="capacity" min="0" required>
                </div>
                <div style="margin-top:18px; display:flex; justify-content:flex-end; gap:10px;">
                    <button class="btn btn-secondary" type="button" onclick="closeModal()">Cancel</button>
                    <button class="btn btn-primary" type="submit">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(room) {
            document.getElementById('edit_room_id').value = room.id;
            document.getElementById('edit_building').value = room.building;
            document.getElementById('edit_room_number').value = room.room_number;
            document.getElementById('edit_capacity').value = room.capacity;
            document.getElementById('editModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('editModal').classList.remove('active');
        }
    </script>
</body>
</html>
