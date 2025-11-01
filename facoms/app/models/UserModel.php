<?php
require_once __DIR__ . '/../../config.php';

class UserModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // ✅ Login function (secure)
    public function login($username, $password) {
        $query = "SELECT * FROM users WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['password'] === $password) { // simple plain text check for now
            return $user;
        }
        return false;
    }

    // ✅ Add new faculty
    public function addFaculty($fname, $lname, $email, $username, $password, $role) {
        $query = "INSERT INTO users (first_name, last_name, email, username, password, role, created_at)
                  VALUES (:fname, :lname, :email, :username, :password, :role, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":fname", $fname);
        $stmt->bindParam(":lname", $lname);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password", $password);
        $stmt->bindParam(":role", $role);
        return $stmt->execute();
    }


    // Delete faculty by ID and remove photo file
        public function deleteFaculty($id) {
        // Get the faculty's photo filename
        $query = "SELECT photo FROM users WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $faculty = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($faculty && !empty($faculty['photo'])) {
            $photoPath = __DIR__ . '/../../uploads/' . $faculty['photo'];

            // Debug: check if file exists
            if (file_exists($photoPath)) {
                if (unlink($photoPath)) {
                    // Photo deleted successfully
                    // echo "Photo deleted: " . $faculty['photo'];
                } else {
                    // Failed to delete
                    // echo "Failed to delete photo: " . $faculty['photo'];
                }
            } else {
                // echo "Photo file not found: " . $photoPath;
            }
        }

        // Delete the faculty record
        $query = "DELETE FROM users WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }




    // ✅ Update faculty info + photo
    public function updateFaculty($id, $first_name, $last_name, $email, $username, $photo = null) {
        if ($photo) {
            $query = "UPDATE users 
                      SET first_name=:first_name, last_name=:last_name, email=:email, username=:username, photo=:photo 
                      WHERE id=:id";
        } else {
            $query = "UPDATE users 
                      SET first_name=:first_name, last_name=:last_name, email=:email, username=:username 
                      WHERE id=:id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':username', $username);
        if ($photo) $stmt->bindParam(':photo', $photo);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // ✅ Fetch all faculty users
    public function getAllFaculty() {
        $query = "SELECT * FROM users WHERE role = 'faculty'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
