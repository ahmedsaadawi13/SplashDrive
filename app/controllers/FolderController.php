<?php
// FILE: /app/controllers/FolderController.php

/**
 * FolderController
 * Handles folder operations (create, delete, rename, move)
 */
class FolderController extends Controller {
    private $folderModel;
    private $activityModel;

    public function __construct() {
        Auth::require();
        $this->folderModel = $this->model('Folder');
        $this->activityModel = $this->model('ActivityLog');
    }

    /**
     * List all folders
     */
    public function index() {
        $tenantId = Auth::tenantId();
        $folders = $this->folderModel->getRootFolders($tenantId);

        $this->view('folders/index', [
            'title' => 'Folders - ' . APP_NAME,
            'folders' => $folders
        ]);
    }

    /**
     * Create folder
     */
    public function create() {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
            return;
        }

        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token'], 403);
            return;
        }

        $tenantId = Auth::tenantId();
        $userId = Auth::id();
        $name = $this->post('name');
        $parentId = $this->post('parent_id');

        // Validate name
        if (empty($name)) {
            $this->json(['success' => false, 'message' => 'Folder name is required'], 400);
            return;
        }

        // Validate parent folder if provided
        if ($parentId) {
            $parent = $this->folderModel->getWithDetails($parentId, $tenantId);
            if (!$parent) {
                $this->json(['success' => false, 'message' => 'Invalid parent folder'], 400);
                return;
            }
        }

        // Create folder
        $folderId = $this->folderModel->createFolder([
            'tenant_id' => $tenantId,
            'parent_id' => $parentId ?: null,
            'name' => $name,
            'created_by' => $userId
        ]);

        // Log activity
        $this->activityModel->log([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'action_type' => ACTION_FOLDER_CREATE,
            'target_type' => 'folder',
            'target_id' => $folderId,
            'description' => 'Created folder: ' . $name
        ]);

        $this->json([
            'success' => true,
            'message' => 'Folder created successfully',
            'folder' => [
                'id' => $folderId,
                'name' => $name
            ]
        ]);
    }

    /**
     * Delete folder (soft delete)
     */
    public function delete($folderId) {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
            return;
        }

        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token'], 403);
            return;
        }

        $tenantId = Auth::tenantId();
        $userId = Auth::id();

        // Get folder
        $folder = $this->folderModel->getWithDetails($folderId, $tenantId);

        if (!$folder) {
            $this->json(['success' => false, 'message' => 'Folder not found'], 404);
            return;
        }

        // Soft delete
        $this->folderModel->softDelete($folderId, $userId);

        // Log activity
        $this->activityModel->log([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'action_type' => ACTION_FOLDER_DELETE,
            'target_type' => 'folder',
            'target_id' => $folderId,
            'description' => 'Deleted folder: ' . $folder['name']
        ]);

        $this->json([
            'success' => true,
            'message' => 'Folder moved to trash'
        ]);
    }

    /**
     * Rename folder
     */
    public function rename($folderId) {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
            return;
        }

        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token'], 403);
            return;
        }

        $tenantId = Auth::tenantId();
        $userId = Auth::id();
        $newName = $this->post('name');

        // Get folder
        $folder = $this->folderModel->getWithDetails($folderId, $tenantId);

        if (!$folder) {
            $this->json(['success' => false, 'message' => 'Folder not found'], 404);
            return;
        }

        // Validate name
        if (empty($newName)) {
            $this->json(['success' => false, 'message' => 'Folder name is required'], 400);
            return;
        }

        // Rename folder
        $this->folderModel->rename($folderId, $newName);

        // Log activity
        $this->activityModel->log([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'action_type' => ACTION_FOLDER_RENAME,
            'target_type' => 'folder',
            'target_id' => $folderId,
            'description' => 'Renamed folder from "' . $folder['name'] . '" to "' . $newName . '"'
        ]);

        $this->json([
            'success' => true,
            'message' => 'Folder renamed successfully'
        ]);
    }

    /**
     * Move folder to new parent
     */
    public function move($folderId) {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
            return;
        }

        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token'], 403);
            return;
        }

        $tenantId = Auth::tenantId();
        $userId = Auth::id();
        $targetParentId = $this->post('parent_id');

        // Get folder
        $folder = $this->folderModel->getWithDetails($folderId, $tenantId);

        if (!$folder) {
            $this->json(['success' => false, 'message' => 'Folder not found'], 404);
            return;
        }

        // Validate target parent folder
        if ($targetParentId) {
            $parent = $this->folderModel->getWithDetails($targetParentId, $tenantId);
            if (!$parent) {
                $this->json(['success' => false, 'message' => 'Invalid target folder'], 400);
                return;
            }

            // Prevent moving folder into itself or its children
            if ($targetParentId == $folderId) {
                $this->json(['success' => false, 'message' => 'Cannot move folder into itself'], 400);
                return;
            }
        }

        // Move folder
        $this->folderModel->move($folderId, $targetParentId);

        // Log activity
        $this->activityModel->log([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'action_type' => ACTION_FOLDER_MOVE,
            'target_type' => 'folder',
            'target_id' => $folderId,
            'description' => 'Moved folder: ' . $folder['name']
        ]);

        $this->json([
            'success' => true,
            'message' => 'Folder moved successfully'
        ]);
    }
}
