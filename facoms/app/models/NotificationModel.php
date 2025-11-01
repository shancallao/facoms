<?php
require_once __DIR__ . '/../../config.php';

class NotificationModel
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getUnreadNotifications($userId, $limit = 5)
    {
        $query = "SELECT id, title, message, link, created_at
                  FROM notifications
                  WHERE user_id = :user_id AND is_read = 0
                  ORDER BY created_at DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllNotifications($userId)
    {
        $query = "SELECT id, title, message, link, is_read, created_at
                  FROM notifications
                  WHERE user_id = :user_id
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markAsRead($id)
    {
        $stmt = $this->conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function createNotification($data)
    {
        $query = "INSERT INTO notifications (user_id, title, message, link, is_read, created_at)
                  VALUES (:user_id, :title, :message, :link, 0, NOW())";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':message', $data['message']);
        $stmt->bindParam(':link', $data['link']);
        return $stmt->execute();
    }
}

