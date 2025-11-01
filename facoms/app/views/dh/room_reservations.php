<?php
require_once __DIR__ . '/../../controllers/RoomReservationController.php';

$controller = new RoomReservationController();
$controller->handleRequest();
$data = $controller->getViewData();

$pending = $data['pending'];
$approved = $data['approved'];
$declined = $data['declined'];
$flash = $data['flash'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Room Reservations</title>
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
            --success: #0c6b3f;
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
        .btn-success { background: var(--success); color: #fff; }

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

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 14px 12px; border-bottom: 1px solid #e2e8f5; text-align: left; }
        th {
            background: #eef3ff;
            color: var(--primary);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .flash { border-radius: 10px; padding: 12px 16px; margin-bottom: 15px; }
        .flash.success { background: #e6f7ee; color: var(--success); border: 1px solid #b1e5c8; }
        .flash.error { background: #ffe9ec; color: var(--danger); border: 1px solid #f5c1ca; }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .status-waiting { background: var(--secondary); color: #1b1b1b; }
        .status-approved { background: #d7f2e3; color: var(--success); }
        .status-declined { background: #ffd6dc; color: var(--danger); }

        .actions { display: flex; gap: 8px; flex-wrap: wrap; }

        @media (max-width: 768px) {
            body { padding: 18px; }
            .toolbar { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <h1>Room Reservations</h1>
        <a class="btn btn-secondary" href="dashboard.php">⬅ Back to Dashboard</a>
    </div>

    <?php foreach ($flash['success'] as $message): ?>
        <div class="flash success">✅ <?= htmlspecialchars($message); ?></div>
    <?php endforeach; ?>

    <?php foreach ($flash['errors'] as $message): ?>
        <div class="flash error">⚠️ <?= htmlspecialchars($message); ?></div>
    <?php endforeach; ?>

    <section class="card">
        <h2>Pending Requests <span class="status-badge status-waiting"><?= count($pending); ?> Waiting</span></h2>
        <?php if ($pending): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Faculty</th>
                            <th>Room</th>
                            <th>Date & Time</th>
                            <th>Reason</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending as $reservation): ?>
                            <tr>
                                <td><?= htmlspecialchars($reservation['faculty_name']); ?></td>
                                <td><?= htmlspecialchars($reservation['building'] . ' ' . $reservation['room_number']); ?></td>
                                <td>
                                    <?= htmlspecialchars(date('M d, Y', strtotime($reservation['date']))); ?><br>
                                    <?= htmlspecialchars(date('h:i A', strtotime($reservation['time_start'])) . ' - ' . date('h:i A', strtotime($reservation['time_end']))); ?>
                                </td>
                                <td><?= nl2br(htmlspecialchars($reservation['reason'] ?? '—')); ?></td>
                                <td>
                                    <div class="actions">
                                        <form method="post" onsubmit="return confirm('Approve this reservation?');">
                                            <input type="hidden" name="reservation_id" value="<?= $reservation['id']; ?>">
                                            <button class="btn btn-success" type="submit" name="action" value="approve">Approve</button>
                                        </form>
                                        <form method="post" onsubmit="return confirm('Decline this reservation?');">
                                            <input type="hidden" name="reservation_id" value="<?= $reservation['id']; ?>">
                                            <button class="btn btn-danger" type="submit" name="action" value="decline">Decline</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align:center; padding:30px; color:var(--text-light);">No pending reservation requests.</div>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Approved Reservations <span class="status-badge status-approved"><?= count($approved); ?> Approved</span></h2>
        <?php if ($approved): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Faculty</th>
                            <th>Room</th>
                            <th>Date & Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($approved as $reservation): ?>
                            <tr>
                                <td><?= htmlspecialchars($reservation['faculty_name']); ?></td>
                                <td><?= htmlspecialchars($reservation['building'] . ' ' . $reservation['room_number']); ?></td>
                                <td>
                                    <?= htmlspecialchars(date('M d, Y', strtotime($reservation['date']))); ?><br>
                                    <?= htmlspecialchars(date('h:i A', strtotime($reservation['time_start'])) . ' - ' . date('h:i A', strtotime($reservation['time_end']))); ?>
                                </td>
                                <td><span class="status-badge status-approved">Approved</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align:center; padding:30px; color:var(--text-light);">No approved reservations yet.</div>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Declined / Cancelled <span class="status-badge status-declined"><?= count($declined); ?> Declined</span></h2>
        <?php if ($declined): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Faculty</th>
                            <th>Room</th>
                            <th>Date & Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($declined as $reservation): ?>
                            <tr>
                                <td><?= htmlspecialchars($reservation['faculty_name']); ?></td>
                                <td><?= htmlspecialchars($reservation['building'] . ' ' . $reservation['room_number']); ?></td>
                                <td>
                                    <?= htmlspecialchars(date('M d, Y', strtotime($reservation['date']))); ?><br>
                                    <?= htmlspecialchars(date('h:i A', strtotime($reservation['time_start'])) . ' - ' . date('h:i A', strtotime($reservation['time_end']))); ?>
                                </td>
                                <td><span class="status-badge status-declined"><?= htmlspecialchars($reservation['status']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align:center; padding:30px; color:var(--text-light);">No declined or cancelled reservations.</div>
        <?php endif; ?>
    </section>
</body>
</html>
