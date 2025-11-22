<?php
// FILE: /app/models/File.php

/**
 * File Model
 * Handles file-related database operations
 */
class File extends Model {
    protected $table = 'files';

    /**
     * Get files by tenant ID
     *
     * @param int $tenantId
     * @param int $folderId Optional folder filter
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getByTenant($tenantId, $folderId = null, $limit = 50, $offset = 0) {
        $sql = "SELECT f.*, u.name as uploaded_by_name,
                       fo.name as folder_name
                FROM {$this->table} f
                LEFT JOIN users u ON f.uploaded_by = u.id
                LEFT JOIN folders fo ON f.folder_id = fo.id
                WHERE f.tenant_id = :tenant_id AND f.is_deleted = 0";

        $params = ['tenant_id' => $tenantId];

        if ($folderId !== null) {
            $sql .= " AND f.folder_id = :folder_id";
            $params['folder_id'] = $folderId;
        }

        $sql .= " ORDER BY f.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Search files by name
     *
     * @param int $tenantId
     * @param string $search
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function search($tenantId, $search, $limit = 50, $offset = 0) {
        $sql = "SELECT f.*, u.name as uploaded_by_name,
                       fo.name as folder_name
                FROM {$this->table} f
                LEFT JOIN users u ON f.uploaded_by = u.id
                LEFT JOIN folders fo ON f.folder_id = fo.id
                WHERE f.tenant_id = :tenant_id
                      AND f.is_deleted = 0
                      AND f.original_filename LIKE :search
                ORDER BY f.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Filter files by type
     *
     * @param int $tenantId
     * @param string $type (document, image, video, other)
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function filterByType($tenantId, $type, $limit = 50, $offset = 0) {
        $mimeTypes = $this->getMimeTypesByCategory($type);

        $placeholders = implode(',', array_fill(0, count($mimeTypes), '?'));

        $sql = "SELECT f.*, u.name as uploaded_by_name,
                       fo.name as folder_name
                FROM {$this->table} f
                LEFT JOIN users u ON f.uploaded_by = u.id
                LEFT JOIN folders fo ON f.folder_id = fo.id
                WHERE f.tenant_id = ?
                      AND f.is_deleted = 0
                      AND f.mime_type IN ($placeholders)
                ORDER BY f.created_at DESC
                LIMIT ? OFFSET ?";

        $params = array_merge([$tenantId], $mimeTypes, [$limit, $offset]);

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Get mime types by category
     *
     * @param string $category
     * @return array
     */
    private function getMimeTypesByCategory($category) {
        $categories = [
            'document' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain'
            ],
            'image' => ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'],
            'video' => ['video/mp4', 'video/mpeg', 'video/quicktime'],
            'archive' => ['application/zip', 'application/x-rar-compressed']
        ];

        return isset($categories[$category]) ? $categories[$category] : [];
    }

    /**
     * Get recent files
     *
     * @param int $tenantId
     * @param int $limit
     * @return array
     */
    public function getRecent($tenantId, $limit = 10) {
        return $this->getByTenant($tenantId, null, $limit, 0);
    }

    /**
     * Soft delete file
     *
     * @param int $fileId
     * @param int $userId
     * @return bool
     */
    public function softDelete($fileId, $userId) {
        $sql = "UPDATE {$this->table}
                SET is_deleted = 1, deleted_at = NOW(), deleted_by = :deleted_by
                WHERE id = :id";
        $this->query($sql, ['id' => $fileId, 'deleted_by' => $userId]);
        return true;
    }

    /**
     * Restore deleted file
     *
     * @param int $fileId
     * @return bool
     */
    public function restore($fileId) {
        $sql = "UPDATE {$this->table}
                SET is_deleted = 0, deleted_at = NULL, deleted_by = NULL
                WHERE id = :id";
        $this->query($sql, ['id' => $fileId]);
        return true;
    }

    /**
     * Get deleted files (trash)
     *
     * @param int $tenantId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getDeleted($tenantId, $limit = 50, $offset = 0) {
        $sql = "SELECT f.*, u.name as uploaded_by_name,
                       du.name as deleted_by_name,
                       fo.name as folder_name
                FROM {$this->table} f
                LEFT JOIN users u ON f.uploaded_by = u.id
                LEFT JOIN users du ON f.deleted_by = du.id
                LEFT JOIN folders fo ON f.folder_id = fo.id
                WHERE f.tenant_id = :tenant_id AND f.is_deleted = 1
                ORDER BY f.deleted_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Move file to folder
     *
     * @param int $fileId
     * @param int $folderId
     * @return bool
     */
    public function move($fileId, $folderId) {
        return $this->update($fileId, ['folder_id' => $folderId]);
    }

    /**
     * Rename file
     *
     * @param int $fileId
     * @param string $newName
     * @return bool
     */
    public function rename($fileId, $newName) {
        return $this->update($fileId, ['original_filename' => $newName]);
    }

    /**
     * Increment download count
     *
     * @param int $fileId
     * @return bool
     */
    public function incrementDownloadCount($fileId) {
        $sql = "UPDATE {$this->table} SET download_count = download_count + 1 WHERE id = :id";
        $this->query($sql, ['id' => $fileId]);
        return true;
    }

    /**
     * Get file with full details
     *
     * @param int $fileId
     * @param int $tenantId
     * @return array|false
     */
    public function getWithDetails($fileId, $tenantId) {
        $sql = "SELECT f.*, u.name as uploaded_by_name,
                       fo.name as folder_name, fo.path as folder_path
                FROM {$this->table} f
                LEFT JOIN users u ON f.uploaded_by = u.id
                LEFT JOIN folders fo ON f.folder_id = fo.id
                WHERE f.id = :id AND f.tenant_id = :tenant_id
                LIMIT 1";
        return $this->fetch($sql, ['id' => $fileId, 'tenant_id' => $tenantId]);
    }
}
