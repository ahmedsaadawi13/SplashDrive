<?php
// FILE: /app/models/Folder.php

/**
 * Folder Model
 * Handles folder hierarchy and operations
 */
class Folder extends Model {
    protected $table = 'folders';

    /**
     * Get folders by tenant ID
     *
     * @param int $tenantId
     * @param int $parentId Optional parent folder filter
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getByTenant($tenantId, $parentId = null, $limit = 50, $offset = 0) {
        $sql = "SELECT f.*, u.name as created_by_name,
                       (SELECT COUNT(*) FROM folders WHERE parent_id = f.id AND is_deleted = 0) as subfolder_count,
                       (SELECT COUNT(*) FROM files WHERE folder_id = f.id AND is_deleted = 0) as file_count
                FROM {$this->table} f
                LEFT JOIN users u ON f.created_by = u.id
                WHERE f.tenant_id = :tenant_id AND f.is_deleted = 0";

        $params = ['tenant_id' => $tenantId];

        if ($parentId === null) {
            $sql .= " AND f.parent_id IS NULL";
        } else {
            $sql .= " AND f.parent_id = :parent_id";
            $params['parent_id'] = $parentId;
        }

        $sql .= " ORDER BY f.name ASC LIMIT :limit OFFSET :offset";

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
     * Get root folders
     *
     * @param int $tenantId
     * @return array
     */
    public function getRootFolders($tenantId) {
        return $this->getByTenant($tenantId, null);
    }

    /**
     * Get folder with details
     *
     * @param int $folderId
     * @param int $tenantId
     * @return array|false
     */
    public function getWithDetails($folderId, $tenantId) {
        $sql = "SELECT f.*, u.name as created_by_name,
                       pf.name as parent_name,
                       (SELECT COUNT(*) FROM folders WHERE parent_id = f.id AND is_deleted = 0) as subfolder_count,
                       (SELECT COUNT(*) FROM files WHERE folder_id = f.id AND is_deleted = 0) as file_count
                FROM {$this->table} f
                LEFT JOIN users u ON f.created_by = u.id
                LEFT JOIN folders pf ON f.parent_id = pf.id
                WHERE f.id = :id AND f.tenant_id = :tenant_id
                LIMIT 1";
        return $this->fetch($sql, ['id' => $folderId, 'tenant_id' => $tenantId]);
    }

    /**
     * Create folder
     *
     * @param array $data
     * @return int Folder ID
     */
    public function createFolder($data) {
        // Generate path
        if (isset($data['parent_id']) && $data['parent_id']) {
            $parent = $this->find($data['parent_id']);
            $data['path'] = $parent['path'] . '/' . $data['name'];
        } else {
            $data['path'] = '/' . $data['name'];
        }

        return $this->insert($data);
    }

    /**
     * Soft delete folder
     *
     * @param int $folderId
     * @param int $userId
     * @return bool
     */
    public function softDelete($folderId, $userId) {
        $sql = "UPDATE {$this->table}
                SET is_deleted = 1, deleted_at = NOW(), deleted_by = :deleted_by
                WHERE id = :id";
        $this->query($sql, ['id' => $folderId, 'deleted_by' => $userId]);

        // Also soft delete all subfolders and files
        $this->softDeleteChildren($folderId, $userId);

        return true;
    }

    /**
     * Soft delete all children folders and files
     *
     * @param int $folderId
     * @param int $userId
     */
    private function softDeleteChildren($folderId, $userId) {
        // Delete child folders
        $sql = "UPDATE {$this->table}
                SET is_deleted = 1, deleted_at = NOW(), deleted_by = :deleted_by
                WHERE parent_id = :folder_id";
        $this->query($sql, ['folder_id' => $folderId, 'deleted_by' => $userId]);

        // Delete files in this folder
        $sql = "UPDATE files
                SET is_deleted = 1, deleted_at = NOW(), deleted_by = :deleted_by
                WHERE folder_id = :folder_id";
        $this->query($sql, ['folder_id' => $folderId, 'deleted_by' => $userId]);

        // Recursively delete children of subfolders
        $subfolders = $this->fetchAll(
            "SELECT id FROM {$this->table} WHERE parent_id = :folder_id",
            ['folder_id' => $folderId]
        );

        foreach ($subfolders as $subfolder) {
            $this->softDeleteChildren($subfolder['id'], $userId);
        }
    }

    /**
     * Restore deleted folder
     *
     * @param int $folderId
     * @return bool
     */
    public function restore($folderId) {
        $sql = "UPDATE {$this->table}
                SET is_deleted = 0, deleted_at = NULL, deleted_by = NULL
                WHERE id = :id";
        $this->query($sql, ['id' => $folderId]);

        // Also restore all files in this folder
        $sql = "UPDATE files
                SET is_deleted = 0, deleted_at = NULL, deleted_by = NULL
                WHERE folder_id = :folder_id";
        $this->query($sql, ['folder_id' => $folderId]);

        return true;
    }

    /**
     * Get deleted folders (trash)
     *
     * @param int $tenantId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getDeleted($tenantId, $limit = 50, $offset = 0) {
        $sql = "SELECT f.*, u.name as created_by_name,
                       du.name as deleted_by_name
                FROM {$this->table} f
                LEFT JOIN users u ON f.created_by = u.id
                LEFT JOIN users du ON f.deleted_by = du.id
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
     * Move folder to new parent
     *
     * @param int $folderId
     * @param int $newParentId
     * @return bool
     */
    public function move($folderId, $newParentId) {
        $folder = $this->find($folderId);

        // Generate new path
        if ($newParentId) {
            $parent = $this->find($newParentId);
            $newPath = $parent['path'] . '/' . $folder['name'];
        } else {
            $newPath = '/' . $folder['name'];
        }

        $this->update($folderId, [
            'parent_id' => $newParentId,
            'path' => $newPath
        ]);

        // Update paths of all children
        $this->updateChildrenPaths($folderId, $newPath);

        return true;
    }

    /**
     * Update paths of all children folders
     *
     * @param int $folderId
     * @param string $newParentPath
     */
    private function updateChildrenPaths($folderId, $newParentPath) {
        $children = $this->fetchAll(
            "SELECT id, name FROM {$this->table} WHERE parent_id = :folder_id",
            ['folder_id' => $folderId]
        );

        foreach ($children as $child) {
            $newPath = $newParentPath . '/' . $child['name'];
            $this->update($child['id'], ['path' => $newPath]);
            $this->updateChildrenPaths($child['id'], $newPath);
        }
    }

    /**
     * Rename folder
     *
     * @param int $folderId
     * @param string $newName
     * @return bool
     */
    public function rename($folderId, $newName) {
        $folder = $this->find($folderId);

        // Generate new path
        $pathParts = explode('/', $folder['path']);
        $pathParts[count($pathParts) - 1] = $newName;
        $newPath = implode('/', $pathParts);

        $this->update($folderId, [
            'name' => $newName,
            'path' => $newPath
        ]);

        // Update paths of all children
        $this->updateChildrenPaths($folderId, $newPath);

        return true;
    }

    /**
     * Get breadcrumb path
     *
     * @param int $folderId
     * @return array
     */
    public function getBreadcrumb($folderId) {
        $breadcrumb = [];
        $folder = $this->find($folderId);

        if (!$folder) {
            return $breadcrumb;
        }

        $breadcrumb[] = [
            'id' => $folder['id'],
            'name' => $folder['name']
        ];

        while ($folder['parent_id']) {
            $folder = $this->find($folder['parent_id']);
            if ($folder) {
                array_unshift($breadcrumb, [
                    'id' => $folder['id'],
                    'name' => $folder['name']
                ]);
            }
        }

        return $breadcrumb;
    }
}
