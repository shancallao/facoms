<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'department_head') {
    header("Location: /facoms/");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Department Head Dashboard</title>
</head>
<body>
    <h2>Welcome, Department Head <?php echo $_SESSION['username']; ?>!</h2>

    <nav>
        <ul>
            <li><a href="manage_faculty.php">Manage Faculty</a></li>
            <li><a href="manage_schedule.php">Manage Schedule</a></li>
            <li><a href="manage_rooms.php">Manage Rooms</a></li>
            <li><a href="room_reservations.php">Room Reservation Requests</a></li>
            <li><a href="leave_requests.php">View Leave Requests</a></li>
            <li><a href="/facoms/logout.php">Logout</a></li>
        </ul>
    </nav>
</body>
</html>
