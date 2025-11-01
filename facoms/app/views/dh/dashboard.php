<?php
require_once __DIR__ . '/../../controllers/DashboardController.php';

$controller = new DashboardController();
$controller->handleActions();
$data = $controller->index();

$profile = $data['profile'];
$stats = $data['stats'];
$notifications = $data['notifications'];
$passwordRequests = $data['passwordRequests'];
$pendingReservations = $data['pendingReservations'];

$defaultAvatarSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120"><rect width="120" height="120" rx="60" fill="#0f4cbd"/><text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" font-family="Segoe UI, Arial" font-size="46" fill="#ffffff">DH</text></svg>';
$defaultAvatar = 'data:image/svg+xml;utf8,' . rawurlencode($defaultAvatarSvg);
$photoPath = $profile && $profile['photo']
    ? '/facoms/app/public/assets/uploads/' . htmlspecialchars($profile['photo'])
    : $defaultAvatar;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Department Head Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary: #0a3a7f;
            --secondary: #f3b904;
            --accent: #0f4cbd;
            --light-bg: #f4f6fb;
            --card-bg: #ffffff;
            --text-dark: #1f2a44;
            --text-light: #5a677d;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: var(--light-bg);
            color: var(--text-dark);
        }

        header {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #fff;
            padding: 20px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        header .profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        header .profile img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255,255,255,0.6);
        }

        header .profile-info h1 {
            margin: 0 0 4px;
            font-size: 1.6rem;
        }

        header .profile-info p {
            margin: 0;
            font-size: 0.95rem;
        }

        header nav {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        header nav a,
        .logout-btn {
            background: rgba(255,255,255,0.15);
            color: #fff;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.3s ease;
            border: none;
            cursor: pointer;
        }

        header nav a:hover,
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        main {
            padding: 25px;
            display: grid;
            gap: 25px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .card {
            background: var(--card-bg);
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 12px 26px rgba(15, 50, 86, 0.08);
            position: relative;
        }

        .card h2 {
            margin: 0 0 12px;
            font-size: 1.1rem;
            color: var(--text-dark);
        }

        .stat-value {
            font-size: 2.4rem;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .notifications-list,
        .requests-list,
        .reservations-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .notifications-list li,
        .requests-list li,
        .reservations-list li {
            background: #f7f9ff;
            border-radius: 10px;
            padding: 14px 16px;
            border-left: 4px solid var(--accent);
        }

        .notifications-list li.unread {
            border-left-color: var(--secondary);
            background: #fffdf2;
        }

        .notification-actions {
            margin-top: 8px;
            display: flex;
            gap: 10px;
        }

        .btn-link {
            background: none;
            border: none;
            color: var(--accent);
            font-weight: 600;
            cursor: pointer;
            padding: 0;
        }

        .badge {
            display: inline-block;
            background: var(--secondary);
            color: #1b1b1b;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            margin-left: 8px;
        }

        .empty-state {
            text-align: center;
            padding: 20px;
            color: var(--text-light);
            background: #f0f4ff;
            border-radius: 10px;
        }

        .actions {
            margin-top: 16px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .primary-btn,
        .secondary-btn {
            border: none;
            cursor: pointer;
            font-weight: 600;
            padding: 10px 16px;
            border-radius: 8px;
        }

        .primary-btn {
            background: var(--accent);
            color: #fff;
        }

        .secondary-btn {
            background: #e8eefc;
            color: var(--accent);
        }

        @media (max-width: 768px) {
            header {
                flex-direction: column;
                align-items: flex-start;
            }

            header nav {
                width: 100%;
                justify-content: flex-start;
            }

            .stat-value {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="profile">
            <img src="<?= $photoPath ?>" alt="Department Head Photo" onerror="this.onerror=null;this.src='<?= $defaultAvatar ?>';">
            <div class="profile-info">
                <h1><?= htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']); ?></h1>
                <p><?= htmlspecialchars($profile['email']); ?> ? Department Head</p>
            </div>
        </div>
        <nav>
            <a href="manage_faculty.php">Manage Faculty</a>
            <a href="manage_rooms.php">Manage Rooms</a>
            <a href="manage_schedule.php">Manage Schedule</a>
            <a href="room_reservations.php">Room Reservations</a>
            <button class="logout-btn" onclick="confirmLogout()">Logout</button>
        </nav>
    </header>

    <main>
        <section class="grid">
            <article class="card">
                <h2>Faculty</h2>
                <div class="stat-value"><?= $stats['totalFaculty']; ?></div>
                <div class="stat-label">Total Faculty Accounts</div>
            </article>
            <article class="card">
                <h2>Classes in Progress</h2>
                <div class="stat-value"><?= $stats['classesInProgress']; ?></div>
                <div class="stat-label">Currently running sessions</div>
            </article>
            <article class="card">
                <h2>Rooms</h2>
                <div class="stat-value"><?= $stats['totalRooms']; ?></div>
                <div class="stat-label">Total registered rooms</div>
            </article>
            <article class="card">
                <h2>Available Rooms Now</h2>
                <div class="stat-value"><?= $stats['availableRooms']; ?></div>
                <div class="stat-label">Free rooms at this moment</div>
            </article>
        </section>

        <section class="grid">
            <article class="card">
                <h2>Notifications <span class="badge"><?= count($notifications); ?></span></h2>
                <?php if ($notifications): ?>
                    <ul class="notifications-list">
                        <?php foreach ($notifications as $notification): ?>
                            <li class="unread">
                                <strong><?= htmlspecialchars($notification['title']); ?></strong>
                                <p><?= nl2br(htmlspecialchars($notification['message'])); ?></p>
                                <div class="notification-actions">
                                    <?php if (!empty($notification['link'])): ?>
                                        <a class="btn-link" href="<?= htmlspecialchars($notification['link']); ?>">Open</a>
                                    <?php endif; ?>
                                    <a class="btn-link" href="?mark_notification=<?= $notification['id']; ?>">Mark as read</a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="empty-state">No new notifications right now.</div>
                <?php endif; ?>
            </article>

            <article class="card">
                <h2>Password Requests <span class="badge"><?= count($passwordRequests); ?></span></h2>
                <?php if ($passwordRequests): ?>
                    <ul class="requests-list">
                        <?php foreach ($passwordRequests as $request): ?>
                            <li>
                                <strong><?= htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></strong>
                                <p>Username: <?= htmlspecialchars($request['username']); ?></p>
                                <div class="actions">
                                    <a class="primary-btn" href="manage_faculty.php?highlight=<?= $request['id']; ?>">Reset Password</a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="empty-state">No faculty password requests pending.</div>
                <?php endif; ?>
            </article>
        </section>

        <section class="card">
            <h2>Pending Room Reservations <span class="badge"><?= count($pendingReservations); ?></span></h2>
            <?php if ($pendingReservations): ?>
                <ul class="reservations-list">
                    <?php foreach ($pendingReservations as $reservation): ?>
                        <li>
                            <strong><?= htmlspecialchars($reservation['faculty_name']); ?></strong>
                            <p>
                                Requesting Room <?= htmlspecialchars($reservation['building'] . ' ' . $reservation['room_number']); ?><br>
                                <?= htmlspecialchars(date('M d, Y', strtotime($reservation['date'])) . ' ? ' . date('h:i A', strtotime($reservation['time_start'])) . ' - ' . date('h:i A', strtotime($reservation['time_end']))); ?>
                            </p>
                            <div class="actions">
                                <form action="room_reservations.php" method="post">
                                    <input type="hidden" name="reservation_id" value="<?= $reservation['id']; ?>">
                                    <button class="primary-btn" type="submit" name="action" value="approve">Approve</button>
                                    <button class="secondary-btn" type="submit" name="action" value="decline">Decline</button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="empty-state">No pending room reservations.</div>
            <?php endif; ?>
        </section>
    </main>

    <form id="logoutForm" action="/facoms/logout.php" method="post" style="display:none;"></form>

    <script>
        function confirmLogout() {
            if (confirm('Are you sure you want to log out?')) {
                document.getElementById('logoutForm').submit();
            }
        }
    </script>
</body>
</html>
