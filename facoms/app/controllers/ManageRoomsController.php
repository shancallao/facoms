<?php
require_once __DIR__ . '/../models/RoomModel.php';

class ManageRoomsController
{
    private $roomModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->ensureDepartmentHead();
        $this->roomModel = new RoomModel();
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
                    $this->addError('Unknown action.');
                    $this->redirect();
            }
        }
    }

    public function getViewData()
    {
        $search = isset($_GET['search']) ? trim($_GET['search']) : null;
        $rooms = $this->roomModel->getAllRooms($search);

        return [
            'rooms' => $rooms,
            'search' => $search,
            'flash' => $this->pullFlashMessages()
        ];
    }

    private function handleCreate()
    {
        $payload = $this->sanitizeRoomPayload($_POST);

        if ($this->roomModel->createRoom($payload)) {
            $this->addSuccess('Room added successfully.');
        } else {
            $this->addError('Unable to add room.');
        }

        $this->redirect();
    }

    private function handleUpdate()
    {
        $roomId = (int)($_POST['room_id'] ?? 0);

        if (!$roomId) {
            $this->addError('Missing room identifier.');
            $this->redirect();
        }

        $payload = $this->sanitizeRoomPayload($_POST);

        if ($this->roomModel->updateRoom($roomId, $payload)) {
            $this->addSuccess('Room details updated successfully.');
        } else {
            $this->addError('Unable to update room details.');
        }

        $this->redirect();
    }

    private function handleDelete()
    {
        $roomId = (int)($_POST['room_id'] ?? 0);

        if (!$roomId) {
            $this->addError('Missing room identifier.');
            $this->redirect();
        }

        if ($this->roomModel->deleteRoom($roomId)) {
            $this->addSuccess('Room removed successfully.');
        } else {
            $this->addError('Unable to remove room. Ensure no schedules depend on it.');
        }

        $this->redirect();
    }

    private function sanitizeRoomPayload($data)
    {
        return [
            'building' => trim($data['building'] ?? ''),
            'room_number' => trim($data['room_number'] ?? ''),
            'capacity' => (int)($data['capacity'] ?? 0)
        ];
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
        header('Location: manage_rooms.php');
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

