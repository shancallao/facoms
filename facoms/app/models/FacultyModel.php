<?php
require_once __DIR__ . '/../../config.php';

class FacultyModel
{
    private $conn;
    private $uploadDir;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
        $this->uploadDir = realpath(__DIR__ . '/../../public/assets/uploads');
    }

    public function getAllFaculty($search = null)
    {
        $baseQuery = "SELECT id, first_name, last_name, email, username, photo, password_request, created_at
                       FROM users WHERE role = 'faculty'";

        if ($search) {
            $baseQuery .= " AND (first_name LIKE :search OR last_name LIKE :search OR username LIKE :search OR email LIKE :search)";
        }

        $baseQuery .= " ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($baseQuery);
        if ($search) {
            $like = "%{$search}%";
            $stmt->bindParam(':search', $like, PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalFaculty()
    {
        $stmt = $this->conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'faculty'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['total'] : 0;
    }

    public function getPasswordRequestList()
    {
        $stmt = $this->conn->query("SELECT id, first_name, last_name, username FROM users WHERE role = 'faculty' AND password_request = 1");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createFaculty(array $data)
    {
        $query = "INSERT INTO users (first_name, last_name, email, username, password, role, photo, created_at)
                  VALUES (:first_name, :last_name, :email, :username, :password, 'faculty', :photo, NOW())";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':password', $data['password']);
        $stmt->bindParam(':photo', $data['photo']);

        return $stmt->execute();
    }

    public function updateFaculty($id, array $data)
    {
        $fields = [];
        $params = [':id' => $id];

        foreach (['first_name', 'last_name', 'email', 'username', 'photo', 'password_request'] as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $params[":" . $field] = $data[$field];
            }
        }

        if (!$fields) {
            return false;
        }

        $query = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id AND role = 'faculty'";
        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        return $stmt->execute();
    }

    public function updateFacultyPassword($id, $password)
    {
        $query = "UPDATE users SET password = :password, password_request = 0 WHERE id = :id AND role = 'faculty'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deleteFaculty($id)
    {
        $photo = $this->getFacultyPhoto($id);

        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = :id AND role = 'faculty'");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $deleted = $stmt->execute();

        if ($deleted && $photo) {
            $this->removePhotoFile($photo);
        }

        return $deleted;
    }

    public function getFacultyPhoto($id)
    {
        $query = "SELECT photo FROM users WHERE id = :id AND role = 'faculty'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result && $result['photo'] ? $result['photo'] : null;
    }

    private function removePhotoFile($photo)
    {
        if (!$photo || !$this->uploadDir) {
            return;
        }

        $path = $this->uploadDir . DIRECTORY_SEPARATOR . $photo;

        if (is_file($path)) {
            @unlink($path);
        }
    }
}

