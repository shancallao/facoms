<?php
require_once __DIR__ . '/../../config.php';

class ScheduleModel
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAllSchedules()
    {
        $query = "SELECT s.*, r.building, r.room_number, CONCAT(u.first_name, ' ', u.last_name) AS faculty_name
                  FROM schedules s
                  INNER JOIN rooms r ON r.id = s.room_id
                  INNER JOIN users u ON u.id = s.faculty_id
                  ORDER BY FIELD(day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), time_start";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createSchedule(array $data)
    {
        $query = "INSERT INTO schedules
                  (academic_period, course_code, course_title, room_id, time_start, time_end, day, program, year_level, section, faculty_id, status, created_at)
                  VALUES
                  (:academic_period, :course_code, :course_title, :room_id, :time_start, :time_end, :day, :program, :year_level, :section, :faculty_id, 'Upcoming', NOW())";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':academic_period', $data['academic_period']);
        $stmt->bindParam(':course_code', $data['course_code']);
        $stmt->bindParam(':course_title', $data['course_title']);
        $stmt->bindParam(':room_id', $data['room_id'], PDO::PARAM_INT);
        $stmt->bindParam(':time_start', $data['time_start']);
        $stmt->bindParam(':time_end', $data['time_end']);
        $stmt->bindParam(':day', $data['day']);
        $stmt->bindParam(':program', $data['program']);
        $stmt->bindParam(':year_level', $data['year_level']);
        $stmt->bindParam(':section', $data['section']);
        $stmt->bindParam(':faculty_id', $data['faculty_id'], PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function updateSchedule($id, array $data)
    {
        $query = "UPDATE schedules SET academic_period = :academic_period, course_code = :course_code, course_title = :course_title,
                  room_id = :room_id, time_start = :time_start, time_end = :time_end, day = :day, program = :program,
                  year_level = :year_level, section = :section, faculty_id = :faculty_id WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':academic_period', $data['academic_period']);
        $stmt->bindParam(':course_code', $data['course_code']);
        $stmt->bindParam(':course_title', $data['course_title']);
        $stmt->bindParam(':room_id', $data['room_id'], PDO::PARAM_INT);
        $stmt->bindParam(':time_start', $data['time_start']);
        $stmt->bindParam(':time_end', $data['time_end']);
        $stmt->bindParam(':day', $data['day']);
        $stmt->bindParam(':program', $data['program']);
        $stmt->bindParam(':year_level', $data['year_level']);
        $stmt->bindParam(':section', $data['section']);
        $stmt->bindParam(':faculty_id', $data['faculty_id'], PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function deleteSchedule($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM schedules WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function hasConflict($roomId, $facultyId, $day, $timeStart, $timeEnd, $excludeId = null)
    {
        $query = "SELECT id FROM schedules
                  WHERE day = :day AND (room_id = :room_id OR faculty_id = :faculty_id)
                  AND ((:time_start BETWEEN time_start AND time_end) OR
                       (:time_end BETWEEN time_start AND time_end) OR
                       (time_start BETWEEN :time_start AND :time_end) OR
                       (time_end BETWEEN :time_start AND :time_end))";

        if ($excludeId) {
            $query .= " AND id <> :exclude_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':day', $day);
        $stmt->bindParam(':room_id', $roomId, PDO::PARAM_INT);
        $stmt->bindParam(':faculty_id', $facultyId, PDO::PARAM_INT);
        $stmt->bindParam(':time_start', $timeStart);
        $stmt->bindParam(':time_end', $timeEnd);
        if ($excludeId) {
            $stmt->bindParam(':exclude_id', $excludeId, PDO::PARAM_INT);
        }

        $stmt->execute();
        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function countClassesInProgress($day, $time)
    {
        $query = "SELECT COUNT(*) as total
                  FROM schedules
                  WHERE day = :day
                  AND time_start <= :time AND time_end >= :time
                  AND status IN ('Upcoming', 'Occupied')";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':day', $day);
        $stmt->bindParam(':time', $time);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['total'] : 0;
    }
}

