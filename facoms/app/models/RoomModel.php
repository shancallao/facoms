<?php
require_once __DIR__ . '/../../config.php';

class RoomModel
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAllRooms($search = null)
    {
        $query = "SELECT id, building, room_number, capacity, created_at FROM rooms";

        if ($search) {
            $query .= " WHERE building LIKE :search OR room_number LIKE :search";
        }

        $query .= " ORDER BY building ASC, room_number ASC";

        $stmt = $this->conn->prepare($query);
        if ($search) {
            $like = "%{$search}%";
            $stmt->bindParam(':search', $like, PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalRooms()
    {
        $stmt = $this->conn->query("SELECT COUNT(*) as total FROM rooms");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['total'] : 0;
    }

    public function findRoomById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM rooms WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createRoom(array $data)
    {
        $query = "INSERT INTO rooms (building, room_number, capacity, created_at)
                  VALUES (:building, :room_number, :capacity, NOW())";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':building', $data['building']);
        $stmt->bindParam(':room_number', $data['room_number']);
        $stmt->bindParam(':capacity', $data['capacity'], PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function updateRoom($id, array $data)
    {
        $query = "UPDATE rooms SET building = :building, room_number = :room_number, capacity = :capacity WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':building', $data['building']);
        $stmt->bindParam(':room_number', $data['room_number']);
        $stmt->bindParam(':capacity', $data['capacity'], PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deleteRoom($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM rooms WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getAvailableRoomsCount($date, $time)
    {
        $query = "SELECT COUNT(*) as available
                  FROM rooms r
                  WHERE r.id NOT IN (
                      SELECT room_id FROM schedules
                      WHERE day = :day AND :time BETWEEN time_start AND time_end AND status IN ('Upcoming','Occupied')
                  )";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':day', $this->mapDayOfWeek($date));
        $stmt->bindValue(':time', $time);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['available'] : 0;
    }

    private function mapDayOfWeek($date)
    {
        $day = date('l', strtotime($date));
        $allowed = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return in_array($day, $allowed, true) ? $day : 'Monday';
    }
}

