<?php
// FILE: /app/models/User.php

/**
 * User Model
 * Handles user-related database operations
 */
class User extends Model {
    protected $table = 'users';

    /**
     * Find user by email
     *
     * @param string $email
     * @return array|false
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        return $this->fetch($sql, ['email' => $email]);
    }

    /**
     * Create new user
     *
     * @param array $data
     * @return int User ID
     */
    public function create($data) {
        // Hash password
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        return $this->insert($data);
    }

    /**
     * Verify user password
     *
     * @param string $email
     * @param string $password
     * @return array|false User data or false
     */
    public function verifyLogin($email, $password) {
        $user = $this->findByEmail($email);

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        // Check if user is active
        if ($user['status'] !== 'active') {
            return false;
        }

        return $user;
    }

    /**
     * Get users by tenant ID
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
     * Count users by tenant ID
     *
     * @param int $tenantId
     * @return int
     */
    public function countByTenant($tenantId) {
        return $this->count(['tenant_id' => $tenantId]);
    }

    /**
     * Update last login time
     *
     * @param int $userId
     * @return bool
     */
    public function updateLastLogin($userId) {
        $sql = "UPDATE {$this->table} SET last_login_at = NOW() WHERE id = :id";
        $this->query($sql, ['id' => $userId]);
        return true;
    }

    /**
     * Get all platform admins
     *
     * @return array
     */
    public function getPlatformAdmins() {
        return $this->findAll(['role' => ROLE_PLATFORM_ADMIN]);
    }

    /**
     * Update user password
     *
     * @param int $userId
     * @param string $newPassword
     * @return bool
     */
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE {$this->table} SET password = :password WHERE id = :id";
        $this->query($sql, ['password' => $hashedPassword, 'id' => $userId]);
        return true;
    }

    /**
     * Get user with tenant data
     *
     * @param int $userId
     * @return array|false
     */
    public function getUserWithTenant($userId) {
        $sql = "SELECT u.*, t.name as tenant_name, t.slug as tenant_slug
                FROM {$this->table} u
                LEFT JOIN tenants t ON u.tenant_id = t.id
                WHERE u.id = :id LIMIT 1";
        return $this->fetch($sql, ['id' => $userId]);
    }
}
