<?php
// FILE: /app/models/Tenant.php

/**
 * Tenant Model
 * Handles tenant/organization-related database operations
 */
class Tenant extends Model {
    protected $table = 'tenants';

    /**
     * Find tenant by slug
     *
     * @param string $slug
     * @return array|false
     */
    public function findBySlug($slug) {
        $sql = "SELECT * FROM {$this->table} WHERE slug = :slug LIMIT 1";
        return $this->fetch($sql, ['slug' => $slug]);
    }

    /**
     * Get all active tenants
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getActive($limit = 50, $offset = 0) {
        $sql = "SELECT * FROM {$this->table}
                WHERE status = 'active'
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get tenant with subscription data
     *
     * @param int $tenantId
     * @return array|false
     */
    public function getWithSubscription($tenantId) {
        $sql = "SELECT t.*, ts.id as subscription_id, ts.status as subscription_status,
                       ts.plan_id, sp.name as plan_name, sp.max_storage_bytes,
                       sp.max_users, sp.max_files
                FROM {$this->table} t
                LEFT JOIN tenant_subscriptions ts ON t.id = ts.tenant_id
                LEFT JOIN subscription_plans sp ON ts.plan_id = sp.id
                WHERE t.id = :id LIMIT 1";
        return $this->fetch($sql, ['id' => $tenantId]);
    }

    /**
     * Get tenant storage usage
     *
     * @param int $tenantId
     * @return int Total bytes used
     */
    public function getStorageUsage($tenantId) {
        $sql = "SELECT COALESCE(SUM(file_size), 0) as total_size
                FROM files
                WHERE tenant_id = :tenant_id AND is_deleted = 0";
        $result = $this->fetch($sql, ['tenant_id' => $tenantId]);
        return (int)$result['total_size'];
    }

    /**
     * Get tenant file count
     *
     * @param int $tenantId
     * @return int
     */
    public function getFileCount($tenantId) {
        $sql = "SELECT COUNT(*) as count FROM files
                WHERE tenant_id = :tenant_id AND is_deleted = 0";
        $result = $this->fetch($sql, ['tenant_id' => $tenantId]);
        return (int)$result['count'];
    }

    /**
     * Get tenant user count
     *
     * @param int $tenantId
     * @return int
     */
    public function getUserCount($tenantId) {
        $sql = "SELECT COUNT(*) as count FROM users
                WHERE tenant_id = :tenant_id AND status = 'active'";
        $result = $this->fetch($sql, ['tenant_id' => $tenantId]);
        return (int)$result['count'];
    }

    /**
     * Get tenant statistics
     *
     * @param int $tenantId
     * @return array
     */
    public function getStatistics($tenantId) {
        return [
            'storage_used' => $this->getStorageUsage($tenantId),
            'file_count' => $this->getFileCount($tenantId),
            'user_count' => $this->getUserCount($tenantId),
            'folder_count' => $this->getFolderCount($tenantId)
        ];
    }

    /**
     * Get tenant folder count
     *
     * @param int $tenantId
     * @return int
     */
    public function getFolderCount($tenantId) {
        $sql = "SELECT COUNT(*) as count FROM folders
                WHERE tenant_id = :tenant_id AND is_deleted = 0";
        $result = $this->fetch($sql, ['tenant_id' => $tenantId]);
        return (int)$result['count'];
    }

    /**
     * Check if tenant can add more users
     *
     * @param int $tenantId
     * @return bool
     */
    public function canAddUser($tenantId) {
        $tenant = $this->getWithSubscription($tenantId);
        if (!$tenant) {
            return false;
        }

        $currentUsers = $this->getUserCount($tenantId);
        return $currentUsers < $tenant['max_users'];
    }

    /**
     * Check if tenant has storage available
     *
     * @param int $tenantId
     * @param int $requiredBytes
     * @return bool
     */
    public function hasStorageAvailable($tenantId, $requiredBytes = 0) {
        $tenant = $this->getWithSubscription($tenantId);
        if (!$tenant) {
            return false;
        }

        $currentUsage = $this->getStorageUsage($tenantId);
        return ($currentUsage + $requiredBytes) <= $tenant['max_storage_bytes'];
    }

    /**
     * Get all tenants with statistics (for platform admin)
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAllWithStats($limit = 50, $offset = 0) {
        $sql = "SELECT t.*,
                       sp.name as plan_name,
                       (SELECT COUNT(*) FROM users WHERE tenant_id = t.id) as user_count,
                       (SELECT COUNT(*) FROM files WHERE tenant_id = t.id AND is_deleted = 0) as file_count,
                       (SELECT COALESCE(SUM(file_size), 0) FROM files WHERE tenant_id = t.id AND is_deleted = 0) as storage_used
                FROM {$this->table} t
                LEFT JOIN tenant_subscriptions ts ON t.id = ts.tenant_id
                LEFT JOIN subscription_plans sp ON ts.plan_id = sp.id
                ORDER BY t.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
