<?php
// FILE: /app/models/Notification.php

/**
 * Notification Model
 * Handles notification/email logging (simulated email system)
 */
class Notification extends Model {
    protected $table = 'notifications';

    /**
     * Create notification
     *
     * @param array $data
     * @return int Notification ID
     */
    public function createNotification($data) {
        return $this->insert($data);
    }

    /**
     * Send notification (simulated)
     *
     * @param int $tenantId
     * @param int $userId
     * @param string $type
     * @param string $subject
     * @param string $message
     * @param string $recipientEmail
     * @return int Notification ID
     */
    public function send($tenantId, $userId, $type, $subject, $message, $recipientEmail) {
        $data = [
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'type' => $type,
            'subject' => $subject,
            'message' => $message,
            'recipient_email' => $recipientEmail,
            'is_sent' => 1,
            'sent_at' => date('Y-m-d H:i:s')
        ];

        return $this->insert($data);
    }

    /**
     * Get notifications by tenant
     *
     * @param int $tenantId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getByTenant($tenantId, $limit = 50, $offset = 0) {
        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = :tenant_id
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get notifications by user
     *
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getByUser($userId, $limit = 50, $offset = 0) {
        $sql = "SELECT * FROM {$this->table}
                WHERE user_id = :user_id
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get pending notifications
     *
     * @param int $limit
     * @return array
     */
    public function getPending($limit = 50) {
        return $this->findAll(['is_sent' => 0], 'created_at ASC', $limit);
    }

    /**
     * Mark as sent
     *
     * @param int $notificationId
     * @return bool
     */
    public function markAsSent($notificationId) {
        return $this->update($notificationId, [
            'is_sent' => 1,
            'sent_at' => date('Y-m-d H:i:s')
        ]);
    }
}
