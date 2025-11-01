<?php
require_once __DIR__ . '/../models/ReservationModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';

class RoomReservationController
{
    private $reservationModel;
    private $notificationModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->ensureDepartmentHead();
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

    public function handleRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? null;
            $reservationId = (int)($_POST['reservation_id'] ?? 0);

            if (!$reservationId) {
                $this->addError('Missing reservation identifier.');
                $this->redirect();
            }

            switch ($action) {
                case 'approve':
                    $this->updateStatus($reservationId, 'Approved');
                    break;
                case 'decline':
                    $this->updateStatus($reservationId, 'Declined');
                    break;
                case 'cancel':
                    $this->updateStatus($reservationId, 'Cancelled');
                    break;
                default:
                    $this->addError('Unknown action for reservation.');
            }

            $this->redirect();
        }
    }

    public function getViewData()
    {
        return [
            'pending' => $this->reservationModel->getReservationsByStatus('Waiting'),
            'approved' => $this->reservationModel->getReservationsByStatus('Approved'),
            'declined' => $this->reservationModel->getReservationsByStatus('Declined'),
            'flash' => $this->pullFlashMessages()
        ];
    }

    private function updateStatus($reservationId, $status)
    {
        if ($this->reservationModel->updateReservationStatus($reservationId, $status)) {
            $this->addSuccess("Reservation status updated to {$status}.");
        } else {
            $this->addError('Unable to update reservation status.');
        }
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
        header('Location: room_reservations.php');
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

