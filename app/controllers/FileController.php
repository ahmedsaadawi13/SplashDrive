<?php
// FILE: /app/controllers/FileController.php

/**
 * FileController
 * Handles file operations (upload, download, delete, move, rename, search)
 */
class FileController extends Controller {
    private $fileModel;
    private $folderModel;
    private $tenantModel;
    private $activityModel;

    public function __construct() {
        Auth::require();
        $this->fileModel = $this->model('File');
        $this->folderModel = $this->model('Folder');
        $this->tenantModel = $this->model('Tenant');
        $this->activityModel = $this->model('ActivityLog');
    }

    /**
     * List all files (root)
     */
    public function index() {
        $tenantId = Auth::tenantId();
        $page = (int)$this->get('page', 1);
        $limit = 50;
        $offset = ($page - 1) * $limit;

        // Get root folders
        $folders = $this->folderModel->getRootFolders($tenantId);

        // Get files in root (folder_id IS NULL)
        $files = $this->fileModel->getByTenant($tenantId, null, $limit, $offset);

        $this->view('files/index', [
            'title' => 'My Files - ' . APP_NAME,
            'folders' => $folders,
            'files' => $files,
            'currentFolder' => null,
            'breadcrumb' => []
        ]);
    }

    /**
     * List files in specific folder
     */
    public function folder($folderId) {
        $tenantId = Auth::tenantId();

        // Get folder details
        $folder = $this->folderModel->getWithDetails($folderId, $tenantId);

        if (!$folder) {
            $this->setFlash('error', 'Folder not found');
            $this->redirect('/files');
            return;
        }

        // Get subfolders
        $folders = $this->folderModel->getByTenant($tenantId, $folderId);

        // Get files in folder
        $files = $this->fileModel->getByTenant($tenantId, $folderId);

        // Get breadcrumb
        $breadcrumb = $this->folderModel->getBreadcrumb($folderId);

        $this->view('files/index', [
            'title' => $folder['name'] . ' - ' . APP_NAME,
            'folders' => $folders,
            'files' => $files,
            'currentFolder' => $folder,
            'breadcrumb' => $breadcrumb
        ]);
    }

    /**
     * Upload file
     */
    public function upload() {
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
        $folderId = $this->post('folder_id');

        // Check if file was uploaded
        if (!isset($_FILES['file'])) {
            $this->json(['success' => false, 'message' => 'No file uploaded'], 400);
            return;
        }

        $file = $_FILES['file'];

        // Validate folder if provided
        if ($folderId) {
            $folder = $this->folderModel->getWithDetails($folderId, $tenantId);
            if (!$folder) {
                $this->json(['success' => false, 'message' => 'Invalid folder'], 400);
                return;
            }
        }

        // Check storage quota
        if (!$this->tenantModel->hasStorageAvailable($tenantId, $file['size'])) {
            $this->json(['success' => false, 'message' => 'Storage quota exceeded. Please upgrade your plan.'], 403);
            return;
        }

        // Upload file
        $uploader = new Uploader($file);
        $uploadPath = UPLOAD_PATH . '/tenant_' . $tenantId;
        $storedFilename = $uploader->upload($uploadPath);

        if (!$storedFilename) {
            $this->json([
                'success' => false,
                'message' => $uploader->getFirstError()
            ], 400);
            return;
        }

        // Get file info
        $fileInfo = $uploader->getFileInfo();

        // Save to database
        $fileId = $this->fileModel->insert([
            'tenant_id' => $tenantId,
            'folder_id' => $folderId ?: null,
            'original_filename' => $fileInfo['original_name'],
            'stored_filename' => $storedFilename,
            'file_path' => $uploadPath . '/' . $storedFilename,
            'mime_type' => $fileInfo['mime_type'],
            'file_size' => $fileInfo['size'],
            'extension' => $fileInfo['extension'],
            'uploaded_by' => $userId,
            'visibility' => 'private'
        ]);

        // Log activity
        $this->activityModel->log([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'action_type' => ACTION_FILE_UPLOAD,
            'target_type' => 'file',
            'target_id' => $fileId,
            'description' => 'Uploaded file: ' . $fileInfo['original_name']
        ]);

        $this->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'file' => [
                'id' => $fileId,
                'name' => $fileInfo['original_name'],
                'size' => Uploader::formatBytes($fileInfo['size'])
            ]
        ]);
    }

    /**
     * Download file
     */
    public function download($fileId) {
        $tenantId = Auth::tenantId();

        // Get file details
        $file = $this->fileModel->getWithDetails($fileId, $tenantId);

        if (!$file) {
            $this->setFlash('error', 'File not found');
            $this->redirect('/files');
            return;
        }

        // Check if file exists on disk
        $filePath = UPLOAD_PATH . '/tenant_' . $tenantId . '/' . $file['stored_filename'];

        if (!file_exists($filePath)) {
            $this->setFlash('error', 'File not found on server');
            $this->redirect('/files');
            return;
        }

        // Increment download count
        $this->fileModel->incrementDownloadCount($fileId);

        // Log activity
        $this->activityModel->log([
            'tenant_id' => $tenantId,
            'user_id' => Auth::id(),
            'action_type' => ACTION_FILE_DOWNLOAD,
            'target_type' => 'file',
            'target_id' => $fileId,
            'description' => 'Downloaded file: ' . $file['original_filename']
        ]);

        // Send file
        header('Content-Type: ' . $file['mime_type']);
        header('Content-Disposition: attachment; filename="' . $file['original_filename'] . '"');
        header('Content-Length: ' . $file['file_size']);
        readfile($filePath);
        exit;
    }

    /**
     * Delete file (soft delete)
     */
    public function delete($fileId) {
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

        // Get file
        $file = $this->fileModel->getWithDetails($fileId, $tenantId);

        if (!$file) {
            $this->json(['success' => false, 'message' => 'File not found'], 404);
            return;
        }

        // Soft delete
        $this->fileModel->softDelete($fileId, $userId);

        // Log activity
        $this->activityModel->log([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'action_type' => ACTION_FILE_DELETE,
            'target_type' => 'file',
            'target_id' => $fileId,
            'description' => 'Deleted file: ' . $file['original_filename']
        ]);

        $this->json([
            'success' => true,
            'message' => 'File moved to trash'
        ]);
    }

    /**
     * Move file to folder
     */
    public function move($fileId) {
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
        $targetFolderId = $this->post('folder_id');

        // Get file
        $file = $this->fileModel->getWithDetails($fileId, $tenantId);

        if (!$file) {
            $this->json(['success' => false, 'message' => 'File not found'], 404);
            return;
        }

        // Validate target folder
        if ($targetFolderId) {
            $folder = $this->folderModel->getWithDetails($targetFolderId, $tenantId);
            if (!$folder) {
                $this->json(['success' => false, 'message' => 'Invalid target folder'], 400);
                return;
            }
        }

        // Move file
        $this->fileModel->move($fileId, $targetFolderId);

        // Log activity
        $this->activityModel->log([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'action_type' => ACTION_FILE_MOVE,
            'target_type' => 'file',
            'target_id' => $fileId,
            'description' => 'Moved file: ' . $file['original_filename']
        ]);

        $this->json([
            'success' => true,
            'message' => 'File moved successfully'
        ]);
    }

    /**
     * Rename file
     */
    public function rename($fileId) {
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

        // Get file
        $file = $this->fileModel->getWithDetails($fileId, $tenantId);

        if (!$file) {
            $this->json(['success' => false, 'message' => 'File not found'], 404);
            return;
        }

        // Validate name
        if (empty($newName)) {
            $this->json(['success' => false, 'message' => 'File name is required'], 400);
            return;
        }

        // Rename file
        $this->fileModel->rename($fileId, $newName);

        // Log activity
        $this->activityModel->log([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'action_type' => ACTION_FILE_RENAME,
            'target_type' => 'file',
            'target_id' => $fileId,
            'description' => 'Renamed file from "' . $file['original_filename'] . '" to "' . $newName . '"'
        ]);

        $this->json([
            'success' => true,
            'message' => 'File renamed successfully'
        ]);
    }

    /**
     * Search files
     */
    public function search() {
        $tenantId = Auth::tenantId();
        $query = $this->get('q', '');
        $type = $this->get('type', '');

        $files = [];

        if ($query) {
            $files = $this->fileModel->search($tenantId, $query);
        } elseif ($type) {
            $files = $this->fileModel->filterByType($tenantId, $type);
        }

        $this->view('files/search', [
            'title' => 'Search Results - ' . APP_NAME,
            'files' => $files,
            'query' => $query,
            'type' => $type
        ]);
    }
}
