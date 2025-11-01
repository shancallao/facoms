<?php
require_once __DIR__ . '/../../controllers/ManageScheduleController.php';

$controller = new ManageScheduleController();
$controller->handleRequest();
$data = $controller->getViewData();

$rooms = $data['rooms'];
$faculty = $data['faculty'];
$schedules = $data['schedules'];
$days = $data['days'];
$flash = $data['flash'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Schedule</title>
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
        input[type="time"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #d8dfea;
            border-radius: 8px;
            font-size: 0.95rem;
            color: var(--text-dark);
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
        .flash.success { background: #e6f7ee; color: #0c6b3f; border: 1px solid #b1e5c8; }
        .flash.error { background: #ffe9ec; color: #a32036; border: 1px solid #f5c1ca; }

        .actions { display: flex; gap: 8px; flex-wrap: wrap; }

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
            max-width: 520px;
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

        .confirmation-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .confirmation-list li {
            margin-bottom: 8px;
        }

        @media (max-width: 768px) {
            body { padding: 18px; }
            .toolbar { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <h1>Manage Schedule</h1>
        <a class="btn btn-secondary" href="dashboard.php">⬅ Back to Dashboard</a>
    </div>

    <?php foreach ($flash['success'] as $message): ?>
        <div class="flash success">✅ <?= htmlspecialchars($message); ?></div>
    <?php endforeach; ?>

    <?php foreach ($flash['errors'] as $message): ?>
        <div class="flash error">⚠️ <?= htmlspecialchars($message); ?></div>
    <?php endforeach; ?>

    <section class="card">
        <h2>Create Class Schedule</h2>
        <form id="createScheduleForm" method="post">
            <input type="hidden" name="action" value="create">
            <div style="display:grid; gap:16px; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
                <div>
                    <label for="academic_period">Academic Period</label>
                    <input type="text" id="academic_period" name="academic_period" placeholder="e.g. 2nd Semester 2025-2026" required>
                </div>
                <div>
                    <label for="course_code">Course Code</label>
                    <input type="text" id="course_code" name="course_code" required>
                </div>
                <div>
                    <label for="course_title">Course Title</label>
                    <input type="text" id="course_title" name="course_title" required>
                </div>
                <div>
                    <label for="room_id">Room</label>
                    <select id="room_id" name="room_id" required>
                        <option value="">Select room</option>
                        <?php foreach ($rooms as $room): ?>
                            <option value="<?= $room['id']; ?>"><?= htmlspecialchars($room['building'] . ' - ' . $room['room_number']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="time_start">Time Start</label>
                    <input type="time" id="time_start" name="time_start" required>
                </div>
                <div>
                    <label for="time_end">Time End</label>
                    <input type="time" id="time_end" name="time_end" required>
                </div>
                <div>
                    <label for="day">Day</label>
                    <select id="day" name="day" required>
                        <?php foreach ($days as $day): ?>
                            <option value="<?= $day; ?>"><?= $day; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="program">Program</label>
                    <input type="text" id="program" name="program" placeholder="e.g. BSIT" required>
                </div>
                <div>
                    <label for="year_level">Year</label>
                    <input type="text" id="year_level" name="year_level" placeholder="e.g. 4th Year" required>
                </div>
                <div>
                    <label for="section">Section</label>
                    <input type="text" id="section" name="section" placeholder="e.g. A" required>
                </div>
                <div>
                    <label for="faculty_id">Faculty</label>
                    <select id="faculty_id" name="faculty_id" required>
                        <option value="">Select faculty</option>
                        <?php foreach ($faculty as $member): ?>
                            <option value="<?= $member['id']; ?>"><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div style="margin-top:18px; display:flex; justify-content:flex-end;">
                <button class="btn btn-primary" type="button" onclick="showConfirmation('createScheduleForm')">Submit Schedule</button>
            </div>
        </form>
    </section>

    <section class="card">
        <h2>Existing Schedules</h2>
        <?php if ($schedules): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Time & Day</th>
                            <th>Room</th>
                            <th>Program / Section</th>
                            <th>Faculty</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $schedule): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($schedule['course_code']); ?></strong><br>
                                    <?= htmlspecialchars($schedule['course_title']); ?><br>
                                    <small><?= htmlspecialchars($schedule['academic_period']); ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($schedule['day']); ?><br>
                                    <?= htmlspecialchars(date('h:i A', strtotime($schedule['time_start'])) . ' - ' . date('h:i A', strtotime($schedule['time_end']))); ?>
                                </td>
                                <td><?= htmlspecialchars($schedule['building'] . ' ' . $schedule['room_number']); ?></td>
                                <td><?= htmlspecialchars($schedule['program'] . ' · ' . $schedule['year_level'] . ' ' . $schedule['section']); ?></td>
                                <td><?= htmlspecialchars($schedule['faculty_name']); ?></td>
                                <td><?= htmlspecialchars($schedule['status']); ?></td>
                                <td>
                                    <div class="actions">
                                        <button class="btn btn-secondary" type="button" data-schedule='<?= htmlspecialchars(json_encode($schedule), ENT_QUOTES, 'UTF-8'); ?>' onclick="openEditSchedule(this)">Edit</button>
                                        <form method="post" onsubmit="return confirm('Remove this schedule?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="schedule_id" value="<?= $schedule['id']; ?>">
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
            <div style="text-align:center; padding:30px; color:var(--text-light);">No schedules created yet.</div>
        <?php endif; ?>
    </section>

    <div id="confirmModal" class="modal-backdrop">
        <div class="modal">
            <button class="close-btn" onclick="closeConfirm()">×</button>
            <h2>Confirm Schedule Details</h2>
            <ul class="confirmation-list" id="confirmationDetails"></ul>
            <div style="margin-top:18px; display:flex; justify-content:flex-end; gap:10px;">
                <button class="btn btn-secondary" type="button" onclick="closeConfirm()">Back</button>
                <button class="btn btn-primary" type="button" onclick="submitPendingForm()">Confirm</button>
            </div>
        </div>
    </div>

    <div id="editModal" class="modal-backdrop">
        <div class="modal">
            <button class="close-btn" onclick="closeEditModal()">×</button>
            <h2>Edit Schedule</h2>
            <form id="editScheduleForm" method="post">
                <input type="hidden" name="action" value="update">
                <input type="hidden" id="edit_schedule_id" name="schedule_id">
                <div style="display:grid; gap:16px; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
                    <div>
                        <label for="edit_academic_period">Academic Period</label>
                        <input type="text" id="edit_academic_period" name="academic_period" required>
                    </div>
                    <div>
                        <label for="edit_course_code">Course Code</label>
                        <input type="text" id="edit_course_code" name="course_code" required>
                    </div>
                    <div>
                        <label for="edit_course_title">Course Title</label>
                        <input type="text" id="edit_course_title" name="course_title" required>
                    </div>
                    <div>
                        <label for="edit_room_id">Room</label>
                        <select id="edit_room_id" name="room_id" required>
                            <option value="">Select room</option>
                            <?php foreach ($rooms as $room): ?>
                                <option value="<?= $room['id']; ?>"><?= htmlspecialchars($room['building'] . ' - ' . $room['room_number']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="edit_time_start">Time Start</label>
                        <input type="time" id="edit_time_start" name="time_start" required>
                    </div>
                    <div>
                        <label for="edit_time_end">Time End</label>
                        <input type="time" id="edit_time_end" name="time_end" required>
                    </div>
                    <div>
                        <label for="edit_day">Day</label>
                        <select id="edit_day" name="day" required>
                            <?php foreach ($days as $day): ?>
                                <option value="<?= $day; ?>"><?= $day; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="edit_program">Program</label>
                        <input type="text" id="edit_program" name="program" required>
                    </div>
                    <div>
                        <label for="edit_year_level">Year</label>
                        <input type="text" id="edit_year_level" name="year_level" required>
                    </div>
                    <div>
                        <label for="edit_section">Section</label>
                        <input type="text" id="edit_section" name="section" required>
                    </div>
                    <div>
                        <label for="edit_faculty_id">Faculty</label>
                        <select id="edit_faculty_id" name="faculty_id" required>
                            <option value="">Select faculty</option>
                            <?php foreach ($faculty as $member): ?>
                                <option value="<?= $member['id']; ?>"><?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div style="margin-top:18px; display:flex; justify-content:flex-end; gap:10px;">
                    <button class="btn btn-secondary" type="button" onclick="closeEditModal()">Cancel</button>
                    <button class="btn btn-primary" type="button" onclick="showConfirmation('editScheduleForm')">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let pendingFormId = null;

        function showConfirmation(formId) {
            const form = document.getElementById(formId);
            pendingFormId = formId;
            const details = [];

            const entries = new FormData(form);
            details.push(`Academic Period: ${entries.get('academic_period')}`);
            details.push(`Course: ${entries.get('course_code')} - ${entries.get('course_title')}`);

            const roomSelect = form.querySelector('[name="room_id"]');
            const roomLabel = roomSelect ? roomSelect.options[roomSelect.selectedIndex]?.text : '';
            if (roomLabel) details.push(`Room: ${roomLabel}`);

            details.push(`Time: ${entries.get('day')} ${entries.get('time_start')} - ${entries.get('time_end')}`);
            details.push(`Program: ${entries.get('program')} ${entries.get('year_level')} ${entries.get('section')}`);

            const facultySelect = form.querySelector('[name="faculty_id"]');
            const facultyLabel = facultySelect ? facultySelect.options[facultySelect.selectedIndex]?.text : '';
            if (facultyLabel) details.push(`Faculty: ${facultyLabel}`);

            const list = document.getElementById('confirmationDetails');
            list.innerHTML = details.map(item => `<li>${item}</li>`).join('');
            document.getElementById('confirmModal').classList.add('active');
        }

        function closeConfirm() {
            document.getElementById('confirmModal').classList.remove('active');
            pendingFormId = null;
        }

        function submitPendingForm() {
            if (!pendingFormId) return;
            document.getElementById(pendingFormId).submit();
        }

        function openEditSchedule(button) {
            const data = JSON.parse(button.dataset.schedule);
            document.getElementById('edit_schedule_id').value = data.id;
            document.getElementById('edit_academic_period').value = data.academic_period;
            document.getElementById('edit_course_code').value = data.course_code;
            document.getElementById('edit_course_title').value = data.course_title;
            document.getElementById('edit_room_id').value = data.room_id;
            document.getElementById('edit_time_start').value = data.time_start;
            document.getElementById('edit_time_end').value = data.time_end;
            document.getElementById('edit_day').value = data.day;
            document.getElementById('edit_program').value = data.program;
            document.getElementById('edit_year_level').value = data.year_level;
            document.getElementById('edit_section').value = data.section;
            document.getElementById('edit_faculty_id').value = data.faculty_id;
            document.getElementById('editModal').classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
        }
    </script>
</body>
</html>
