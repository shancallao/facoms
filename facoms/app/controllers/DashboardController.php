<?php
require_once __DIR__ . '/../models/DepartmentHeadModel.php';
require_once __DIR__ . '/../models/FacultyModel.php';
require_once __DIR__ . '/../models/RoomModel.php';
require_once __DIR__ . '/../models/ScheduleModel.php';
require_once __DIR__ . '/../models/ReservationModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';

class DashboardController
{
    private $departmentHeadModel;
    private $facultyModel;
    private $roomModel;
    private $scheduleModel;
    private $reservationModel;
    private $notificationModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->ensureDepartmentHead();

        $this->departmentHeadModel = new DepartmentHeadModel();
        $this->facultyModel = new FacultyModel();
        $this->roomModel = new RoomModel();
        $this->scheduleModel = new ScheduleModel();
        $this->reservationModel = new ReservationModel();
        $this->notificationModel = new NotificationModel();
    }

    private function ensureDepartmentHead()
    {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'department_head') {
            header('Location: /facoms/');
            exit;
        }
    }

    public function handleActions()
    {
        if (isset($_GET['mark_notification'])) {
            $notificationId = (int)$_GET['mark_notification'];
            $this->notificationModel->markAsRead($notificationId);
            header('Location: dashboard.php');
            exit;
        }
    }

    public function index()
    {
        $userId = $_SESSION['id'];
        $profile = $this->departmentHeadModel->getProfileById($userId);

        $today = date('Y-m-d');
        $currentTime = date('H:i:s');
        $currentDay = date('l');

        $stats = [
            'totalFaculty' => $this->facultyModel->getTotalFaculty(),
            'classesInProgress' => $this->scheduleModel->countClassesInProgress($currentDay, $currentTime),
            'totalRooms' => $this->roomModel->getTotalRooms(),
            'availableRooms' => $this->roomModel->getAvailableRoomsCount($today, $currentTime)
        ];

        $notifications = $this->notificationModel->getUnreadNotifications($userId, 6);
        $passwordRequests = $this->facultyModel->getPasswordRequestList();
        $pendingReservations = $this->reservationModel->getPendingReservations();

        return [
            'profile' => $profile,
            'stats' => $stats,
            'notifications' => $notifications,
            'passwordRequests' => $passwordRequests,
            'pendingReservations' => $pendingReservations
        ];
    }
}

