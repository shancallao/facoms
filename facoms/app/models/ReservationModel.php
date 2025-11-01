<?php
require_once __DIR__ . '/../../config.php';

class ReservationModel
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getReservationsByStatus($status = null)
    {
        $query = "SELECT res.*, CONCAT(f.first_name, ' ', f.last_name) AS faculty_name,
                         r.building, r.room_number, s.course_code
                  FROM reservations res
                  INNER JOIN users f ON f.id = res.faculty_id
                  INNER JOIN rooms r ON r.id = res.room_id
                  LEFT JOIN schedules s ON s.id = res.schedule_id";

        if ($status) {
            $query .= " WHERE res.status = :status";
        }

        $query .= " ORDER BY res.created_at DESC";

        $stmt = $this->conn->prepare($query);

        if ($status) {
            $stmt->bindParam(':status', $status);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPendingReservations()
    {
        return $this->getReservationsByStatus('Waiting');
    }

    public function updateReservationStatus($id, $status)
    {
        $query = "UPDATE reservations SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}

