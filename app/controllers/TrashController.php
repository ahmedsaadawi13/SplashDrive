<?php
// FILE: /app/controllers/TrashController.php

/**
 * TrashController
 * Handles trash/recycle bin operations
 */
class TrashController extends Controller {
    private $fileModel;
    private $folderModel;
    private $activityModel;

    public function __construct() {
        Auth::require();
        $this->fileModel = $this->model('File');
        $this->folderModel = $this->model('Folder');
        $this->activityModel = $this->model('ActivityLog');
    }

    /**
     * View trash
     */
    public function index() {
        $tenantId = Auth::tenantId();

        // Get deleted files and folders
        $deletedFiles = $this->fileModel->getDeleted($tenantId);
        $deletedFolders = $this->folderModel->getDeleted($tenantId);

        $this->view('trash/index', [
            'title' => 'Trash - ' . APP_NAME,
            'deletedFiles' => $deletedFiles,
            'deletedFolders' => $deletedFolders
        ]);
    }

    /**
     * Restore item from trash
     */
    public function restore($type, $id) {
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

        if ($type === 'file') {
            $item = $this->fileModel->find($id);
            if (!$item || $item['tenant_id'] != $tenantId) {
                $this->json(['success' => false, 'message' => 'File not found'], 404);
                return;
            }

            $this->fileModel->restore($id);
            $actionType = ACTION_FILE_RESTORE;
            $itemName = $item['original_filename'];
        } elseif ($type === 'folder') {
            $item = $this->folderModel->find($id);
            if (!$item || $item['tenant_id'] != $tenantId) {
                $this->json(['success' => false, 'message' => 'Folder not found'], 404);
                return;
            }

            $this->folderModel->restore($id);
            $actionType = ACTION_FOLDER_RESTORE;
            $itemName = $item['name'];
        } else {
            $this->json(['success' => false, 'message' => 'Invalid type'], 400);
            return;
        }

        // Log activity
        $this->activityModel->log([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'action_type' => $actionType,
            'target_type' => $type,
            'target_id' => $id,
            'description' => 'Restored ' . $type . ': ' . $itemName
        ]);

        $this->json([
            'success' => true,
            'message' => ucfirst($type) . ' restored successfully'
        ]);
    }

    /**
     * Permanently delete item
     */
    public function deletePermanently($type, $id) {
        Auth::requirePermission('delete_permanently');

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

        if ($type === 'file') {
            $item = $this->fileModel->find($id);
            if (!$item || $item['tenant_id'] != $tenantId) {
                $this->json(['success' => false, 'message' => 'File not found'], 404);
                return;
            }

            // Delete physical file
            $filePath = UPLOAD_PATH . '/tenant_' . $tenantId . '/' . $item['stored_filename'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Delete from database
            $this->fileModel->delete($id);
        } elseif ($type === 'folder') {
            $item = $this->folderModel->find($id);
            if (!$item || $item['tenant_id'] != $tenantId) {
                $this->json(['success' => false, 'message' => 'Folder not found'], 404);
                return;
            }

            // Delete from database
            $this->folderModel->delete($id);
        } else {
            $this->json(['success' => false, 'message' => 'Invalid type'], 400);
            return;
        }

        $this->json([
            'success' => true,
            'message' => ucfirst($type) . ' deleted permanently'
        ]);
    }

    /**
     * Empty trash
     */
    public function empty() {
        Auth::requirePermission('delete_permanently');

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

        // Get all deleted files
        $deletedFiles = $this->fileModel->getDeleted($tenantId, 1000, 0);

        foreach ($deletedFiles as $file) {
            // Delete physical file
            $filePath = UPLOAD_PATH . '/tenant_' . $tenantId . '/' . $file['stored_filename'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Delete from database
            $this->fileModel->delete($file['id']);
        }

        // Get all deleted folders
        $deletedFolders = $this->folderModel->getDeleted($tenantId, 1000, 0);

        foreach ($deletedFolders as $folder) {
            $this->folderModel->delete($folder['id']);
        }

        $this->json([
            'success' => true,
            'message' => 'Trash emptied successfully'
        ]);
    }
}
