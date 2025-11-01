<?php
require_once __DIR__ . '/../models/FacultyModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';

class ManageFacultyController
{
    private $facultyModel;
    private $notificationModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->ensureDepartmentHead();
        $this->facultyModel = new FacultyModel();
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

            switch ($action) {
                case 'create':
                    $this->handleCreate();
                    break;
                case 'update':
                    $this->handleUpdate();
                    break;
                case 'reset_password':
                    $this->handlePasswordReset();
                    break;
                case 'delete':
                    $facultyId = (int)($_POST['faculty_id'] ?? 0);
                    $this->handleDelete($facultyId);
                    break;
                default:
                    $this->addError('Invalid action requested.');
                    $this->redirect();
            }
        }

        if (isset($_GET['resolve_request'])) {
            $this->handleResolvePasswordRequest((int)$_GET['resolve_request']);
        }
    }

    public function getViewData()
    {
        $search = isset($_GET['search']) ? trim($_GET['search']) : null;
        $faculty = $this->facultyModel->getAllFaculty($search);

        return [
            'search' => $search,
            'faculty' => $faculty,
            'flash' => $this->pullFlashMessages()
        ];
    }

    private function handleCreate()
    {
        $payload = $this->sanitizeFacultyPayload($_POST);
        $payload['photo'] = $this->handlePhotoUpload($_FILES['photo'] ?? null);

        if ($this->facultyModel->createFaculty($payload)) {
            $this->addSuccess('Faculty account created successfully.');
        } else {
            $this->addError('Unable to create faculty account.');
        }

        $this->redirect();
    }

    private function handleUpdate()
    {
        $id = (int)($_POST['faculty_id'] ?? 0);
        if (!$id) {
            $this->addError('Missing faculty identifier.');
            $this->redirect();
        }

        $payload = $this->sanitizeFacultyPayload($_POST);

        $photo = $this->handlePhotoUpload($_FILES['photo'] ?? null);
        if ($photo) {
            $payload['photo'] = $photo;
        }

        if ($this->facultyModel->updateFaculty($id, $payload)) {
            $this->addSuccess('Faculty details updated successfully.');
        } else {
            $this->addError('No updates were applied.');
        }

        $this->redirect();
    }

    private function handlePasswordReset()
    {
        $id = (int)($_POST['faculty_id'] ?? 0);
        $newPassword = $_POST['new_password'] ?? '';

        if (!$id || !$newPassword) {
            $this->addError('Faculty ID and new password are required.');
            $this->redirect();
        }

        if ($this->facultyModel->updateFacultyPassword($id, $newPassword)) {
            $this->addSuccess('Password updated and request cleared.');
        } else {
            $this->addError('Unable to update password.');
        }

        $this->redirect();
    }

    private function handleDelete($id)
    {
        if (!$id) {
            $this->addError('Missing faculty identifier.');
            $this->redirect();
        }

        if ($this->facultyModel->deleteFaculty($id)) {
            $this->addSuccess('Faculty removed successfully.');
        } else {
            $this->addError('Unable to remove faculty.');
        }

        $this->redirect();
    }

    private function handleResolvePasswordRequest($id)
    {
        if ($this->facultyModel->updateFaculty($id, ['password_request' => 0])) {
            $this->addSuccess('Password request marked as resolved.');
        }

        $this->redirect();
    }

    private function sanitizeFacultyPayload($data)
    {
        return [
            'first_name' => trim($data['first_name'] ?? ''),
            'last_name' => trim($data['last_name'] ?? ''),
            'email' => trim($data['email'] ?? ''),
            'username' => trim($data['username'] ?? ''),
            'password' => trim($data['password'] ?? '')
        ];
    }

    private function handlePhotoUpload($file)
    {
        if (!$file || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions, true)) {
            $this->addError('Invalid photo format. Only JPG and PNG are allowed.');
            return null;
        }

        $filename = uniqid('faculty_', true) . '.' . $extension;
        $destinationDir = realpath(__DIR__ . '/../public/assets/uploads');

        if (!$destinationDir) {
            $destinationDir = __DIR__ . '/../public/assets/uploads';
        }

        $targetPath = $destinationDir . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            $this->addError('Failed to upload photo.');
            return null;
        }

        return $filename;
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
        header('Location: manage_faculty.php');
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

