<?php
// FILE: /app/models/ActivityLog.php

/**
 * ActivityLog Model
 * Handles activity logging and audit trail
 */
class ActivityLog extends Model {
    protected $table = 'activity_logs';

    /**
     * Log an activity
     *
     * @param array $data
     * @return int Log ID
     */
    public function log($data) {
        // Auto-fill IP and user agent if not provided
        if (!isset($data['ip_address'])) {
            $data['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? null;
        }

        if (!isset($data['user_agent'])) {
            $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? null;
        }

        return $this->insert($data);
    }

    /**
     * Get activities by tenant
     *
     * @param int $tenantId
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getByTenant($tenantId, $filters = [], $limit = 50, $offset = 0) {
        $sql = "SELECT al.*, u.name as user_name, u.email as user_email
                FROM {$this->table} al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.tenant_id = :tenant_id";

        $params = ['tenant_id' => $tenantId];

        // Apply filters
        if (isset($filters['user_id'])) {
            $sql .= " AND al.user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        if (isset($filters['action_type'])) {
            $sql .= " AND al.action_type = :action_type";
            $params['action_type'] = $filters['action_type'];
        }

        if (isset($filters['target_type'])) {
            $sql .= " AND al.target_type = :target_type";
            $params['target_type'] = $filters['target_type'];
        }

        if (isset($filters['date_from'])) {
            $sql .= " AND al.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (isset($filters['date_to'])) {
            $sql .= " AND al.created_at <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $sql .= " ORDER BY al.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':' . $key, $value);
            }
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get recent activities
     *
     * @param int $tenantId
     * @param int $limit
     * @return array
     */
    public function getRecent($tenantId, $limit = 20) {
        return $this->getByTenant($tenantId, [], $limit, 0);
    }

    /**
     * Get activities by user
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
     * Count activities by tenant
     *
     * @param int $tenantId
     * @param array $filters
     * @return int
     */
    public function countByTenant($tenantId, $filters = []) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE tenant_id = :tenant_id";
        $params = ['tenant_id' => $tenantId];

        if (isset($filters['user_id'])) {
            $sql .= " AND user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        if (isset($filters['action_type'])) {
            $sql .= " AND action_type = :action_type";
            $params['action_type'] = $filters['action_type'];
        }

        $result = $this->fetch($sql, $params);
        return (int)$result['count'];
    }
}
