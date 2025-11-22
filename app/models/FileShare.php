<?php
// FILE: /app/models/FileShare.php

/**
 * FileShare Model
 * Handles public sharing links for files and folders
 */
class FileShare extends Model {
    protected $table = 'file_shares';

    /**
     * Create share link
     *
     * @param array $data
     * @return int Share ID
     */
    public function createShare($data) {
        // Generate unique token
        $data['token'] = $this->generateUniqueToken();

        return $this->insert($data);
    }

    /**
     * Generate unique token
     *
     * @return string
     */
    private function generateUniqueToken() {
        do {
            $token = bin2hex(random_bytes(32));
            $exists = $this->findByToken($token);
        } while ($exists);

        return $token;
    }

    /**
     * Find share by token
     *
     * @param string $token
     * @return array|false
     */
    public function findByToken($token) {
        $sql = "SELECT * FROM {$this->table} WHERE token = :token LIMIT 1";
        return $this->fetch($sql, ['token' => $token]);
    }

    /**
     * Get share with details
     *
     * @param string $token
     * @return array|false
     */
    public function getByToken($token) {
        $sql = "SELECT fs.*,
                       f.original_filename, f.file_size, f.mime_type, f.file_path,
                       fo.name as folder_name,
                       u.name as created_by_name
                FROM {$this->table} fs
                LEFT JOIN files f ON fs.file_id = f.id
                LEFT JOIN folders fo ON fs.folder_id = fo.id
                LEFT JOIN users u ON fs.created_by = u.id
                WHERE fs.token = :token
                LIMIT 1";
        return $this->fetch($sql, ['token' => $token]);
    }

    /**
     * Check if share is valid and not expired
     *
     * @param string $token
     * @return bool
     */
    public function isValid($token) {
        $share = $this->findByToken($token);

        if (!$share) {
            return false;
        }

        // Check if expired
        if ($share['expires_at'] && strtotime($share['expires_at']) < time()) {
            return false;
        }

        return true;
    }

    /**
     * Get shares by tenant
     *
     * @param int $tenantId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getByTenant($tenantId, $limit = 50, $offset = 0) {
        $sql = "SELECT fs.*,
                       f.original_filename,
                       fo.name as folder_name,
                       u.name as created_by_name
                FROM {$this->table} fs
                LEFT JOIN files f ON fs.file_id = f.id
                LEFT JOIN folders fo ON fs.folder_id = fo.id
                LEFT JOIN users u ON fs.created_by = u.id
                WHERE fs.tenant_id = :tenant_id
                ORDER BY fs.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Increment access count
     *
     * @param int $shareId
     * @return bool
     */
    public function incrementAccessCount($shareId) {
        $sql = "UPDATE {$this->table} SET access_count = access_count + 1 WHERE id = :id";
        $this->query($sql, ['id' => $shareId]);
        return true;
    }

    /**
     * Get shares for a file
     *
     * @param int $fileId
     * @return array
     */
    public function getByFile($fileId) {
        return $this->findAll(['file_id' => $fileId], 'created_at DESC');
    }

    /**
     * Get shares for a folder
     *
     * @param int $folderId
     * @return array
     */
    public function getByFolder($folderId) {
        return $this->findAll(['folder_id' => $folderId], 'created_at DESC');
    }
}
