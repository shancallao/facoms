<?php
require_once __DIR__ . '/../models/ScheduleModel.php';
require_once __DIR__ . '/../models/RoomModel.php';
require_once __DIR__ . '/../models/FacultyModel.php';

class ManageScheduleController
{
    private $scheduleModel;
    private $roomModel;
    private $facultyModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->ensureDepartmentHead();
        $this->scheduleModel = new ScheduleModel();
        $this->roomModel = new RoomModel();
        $this->facultyModel = new FacultyModel();
    }

    private function ensureDepartmentHead()
    {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'department_head') {
            header('Location: /facoms/');
            exit;
        }
    }

    public function handleRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? null;

            switch ($action) {
                case 'create':
                    $this->handleCreate();
                    break;
                case 'update':
                    $this->handleUpdate();
                    break;
                case 'delete':
                    $this->handleDelete();
                    break;
                default:
                    $this->addError('Unknown schedule action.');
                    $this->redirect();
            }
        }
    }

    public function getViewData()
    {
        return [
            'rooms' => $this->roomModel->getAllRooms(),
            'faculty' => $this->facultyModel->getAllFaculty(),
            'schedules' => $this->scheduleModel->getAllSchedules(),
            'days' => ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
            'flash' => $this->pullFlashMessages()
        ];
    }

    private function handleCreate()
    {
        $payload = $this->sanitizeSchedulePayload($_POST);

        if (!$this->isValidTimeRange($payload['time_start'], $payload['time_end'])) {
            $this->addError('Time End must be after Time Start.');
            $this->redirect();
        }

        if ($this->scheduleModel->hasConflict($payload['room_id'], $payload['faculty_id'], $payload['day'], $payload['time_start'], $payload['time_end'])) {
            $this->addError('Schedule conflict detected for the selected room or faculty.');
            $this->redirect();
        }

        if ($this->scheduleModel->createSchedule($payload)) {
            $this->addSuccess('Schedule created successfully.');
        } else {
            $this->addError('Unable to save schedule.');
        }

        $this->redirect();
    }

    private function handleUpdate()
    {
        $scheduleId = (int)($_POST['schedule_id'] ?? 0);
        if (!$scheduleId) {
            $this->addError('Missing schedule identifier.');
            $this->redirect();
        }

        $payload = $this->sanitizeSchedulePayload($_POST);

        if (!$this->isValidTimeRange($payload['time_start'], $payload['time_end'])) {
            $this->addError('Time End must be after Time Start.');
            $this->redirect();
        }

        if ($this->scheduleModel->hasConflict($payload['room_id'], $payload['faculty_id'], $payload['day'], $payload['time_start'], $payload['time_end'], $scheduleId)) {
            $this->addError('Schedule conflict detected for the selected room or faculty.');
            $this->redirect();
        }

        if ($this->scheduleModel->updateSchedule($scheduleId, $payload)) {
            $this->addSuccess('Schedule updated successfully.');
        } else {
            $this->addError('Unable to update schedule.');
        }

        $this->redirect();
    }

    private function handleDelete()
    {
        $scheduleId = (int)($_POST['schedule_id'] ?? 0);

        if (!$scheduleId) {
            $this->addError('Missing schedule identifier.');
            $this->redirect();
        }

        if ($this->scheduleModel->deleteSchedule($scheduleId)) {
            $this->addSuccess('Schedule removed successfully.');
        } else {
            $this->addError('Unable to delete schedule.');
        }

        $this->redirect();
    }

    private function sanitizeSchedulePayload($data)
    {
        return [
            'academic_period' => trim($data['academic_period'] ?? ''),
            'course_code' => trim($data['course_code'] ?? ''),
            'course_title' => trim($data['course_title'] ?? ''),
            'room_id' => (int)($data['room_id'] ?? 0),
            'time_start' => $data['time_start'] ?? '00:00',
            'time_end' => $data['time_end'] ?? '00:00',
            'day' => $data['day'] ?? 'Monday',
            'program' => trim($data['program'] ?? ''),
            'year_level' => trim($data['year_level'] ?? ''),
            'section' => trim($data['section'] ?? ''),
            'faculty_id' => (int)($data['faculty_id'] ?? 0)
        ];
    }

    private function isValidTimeRange($start, $end)
    {
        return strtotime($end) > strtotime($start);
    }

    private function addSuccess($message)
    {
        $_SESSION['flash']['success'][] = $message;
    }

    private function addError($message)
    {
        $_SESSION['flash']['errors'][] = $message;
    }

    private function redirect()
    {
        header('Location: manage_schedule.php');
        exit;
    }

    private function pullFlashMessages()
    {
        if (!isset($_SESSION['flash'])) {
            return ['success' => [], 'errors' => []];
        }

        $messages = $_SESSION['flash'];
        unset($_SESSION['flash']);

        if (!isset($messages['success'])) {
            $messages['success'] = [];
        }

        if (!isset($messages['errors'])) {
            $messages['errors'] = [];
        }

        return $messages;
    }
}

