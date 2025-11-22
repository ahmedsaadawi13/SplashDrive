<?php
// FILE: /app/controllers/ApiController.php

/**
 * ApiController
 * Handles REST API endpoints for file and folder operations
 * Uses API key authentication
 */
class ApiController extends Controller {
    private $apiKeyModel;
    private $fileModel;
    private $folderModel;
    private $tenantModel;
    private $activityModel;
    private $tenant;

    public function __construct() {
        $this->apiKeyModel = $this->model('ApiKey');
        $this->fileModel = $this->model('File');
        $this->folderModel = $this->model('Folder');
        $this->tenantModel = $this->model('Tenant');
        $this->activityModel = $this->model('ActivityLog');

        // Authenticate API request
        $this->authenticateApi();
    }

    /**
     * Authenticate API request using X-API-KEY header
     */
    private function authenticateApi() {
        $apiKey = null;

        // Check for API key in header
        if (isset($_SERVER['HTTP_X_API_KEY'])) {
            $apiKey = $_SERVER['HTTP_X_API_KEY'];
        }

        if (!$apiKey) {
            $this->json(['success' => false, 'message' => 'Missing API key'], 401);
            exit;
        }

        // Verify API key
        $keyData = $this->apiKeyModel->verifyKey($apiKey);

        if (!$keyData) {
            $this->json(['success' => false, 'message' => 'Invalid API key'], 401);
            exit;
        }

        // Check tenant status
        if ($keyData['tenant_status'] !== 'active') {
            $this->json(['success' => false, 'message' => 'Tenant account is not active'], 403);
            exit;
        }

        // Store tenant data
        $this->tenant = $keyData;

        // Update last used time
        $this->apiKeyModel->updateLastUsed($keyData['id']);
    }

    /**
     * List files and folders
     * GET /api/files/list?folder_id=123&page=1&limit=50&search=query
     */
    public function listFiles() {
        $folderId = $this->get('folder_id');
        $page = (int)$this->get('page', 1);
        $limit = min((int)$this->get('limit', 50), 100); // Max 100
        $search = $this->get('search', '');
        $offset = ($page - 1) * $limit;

        $tenantId = $this->tenant['tenant_id'];

        // Get folders
        $folders = [];
        if ($folderId) {
            $folders = $this->folderModel->getByTenant($tenantId, $folderId, $limit, 0);
        } else {
            $folders = $this->folderModel->getRootFolders($tenantId);
        }

        // Get files
        $files = [];
        if ($search) {
            $files = $this->fileModel->search($tenantId, $search, $limit, $offset);
        } else {
            $files = $this->fileModel->getByTenant($tenantId, $folderId, $limit, $offset);
        }

        // Format response
        $response = [
            'success' => true,
            'data' => [
                'folders' => array_map(function($folder) {
                    return [
                        'id' => $folder['id'],
                        'name' => $folder['name'],
                        'path' => $folder['path'],
                        'parent_id' => $folder['parent_id'],
                        'subfolder_count' => $folder['subfolder_count'],
                        'file_count' => $folder['file_count'],
                        'created_at' => $folder['created_at']
                    ];
                }, $folders),
                'files' => array_map(function($file) {
                    return [
                        'id' => $file['id'],
                        'name' => $file['original_filename'],
                        'size' => $file['file_size'],
                        'size_formatted' => Uploader::formatBytes($file['file_size']),
                        'mime_type' => $file['mime_type'],
                        'extension' => $file['extension'],
                        'folder_id' => $file['folder_id'],
                        'uploaded_by' => $file['uploaded_by_name'],
                        'download_count' => $file['download_count'],
                        'created_at' => $file['created_at'],
                        'updated_at' => $file['updated_at']
                    ];
                }, $files)
            ],
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total_files' => count($files)
            ]
        ];

        $this->json($response);
    }

    /**
     * List folders only
     * GET /api/folders/list?parent_id=123
     */
    public function listFolders() {
        $parentId = $this->get('parent_id');
        $tenantId = $this->tenant['tenant_id'];

        // Get folders
        if ($parentId) {
            $folders = $this->folderModel->getByTenant($tenantId, $parentId);
        } else {
            $folders = $this->folderModel->getRootFolders($tenantId);
        }

        // Format response
        $response = [
            'success' => true,
            'data' => [
                'folders' => array_map(function($folder) {
                    return [
                        'id' => $folder['id'],
                        'name' => $folder['name'],
                        'path' => $folder['path'],
                        'parent_id' => $folder['parent_id'],
                        'subfolder_count' => $folder['subfolder_count'],
                        'file_count' => $folder['file_count'],
                        'created_at' => $folder['created_at']
                    ];
                }, $folders)
            ]
        ];

        $this->json($response);
    }

    /**
     * Upload file
     * POST /api/files/upload
     * Content-Type: multipart/form-data
     * Body: file (file), folder_id (optional)
     */
    public function uploadFile() {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request method'], 400);
            return;
        }

        $tenantId = $this->tenant['tenant_id'];
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
            $this->json([
                'success' => false,
                'message' => 'Storage quota exceeded. Please upgrade your plan.'
            ], 403);
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

        // Save to database (use tenant's first user as uploaded_by for API uploads)
        $userModel = $this->model('User');
        $users = $userModel->getByTenant($tenantId, 1, 0);
        $userId = $users[0]['id'] ?? null;

        if (!$userId) {
            $this->json(['success' => false, 'message' => 'No user found for tenant'], 500);
            return;
        }

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
            'description' => 'Uploaded file via API: ' . $fileInfo['original_name']
        ]);

        // Return response
        $this->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'data' => [
                'file' => [
                    'id' => $fileId,
                    'name' => $fileInfo['original_name'],
                    'size' => $fileInfo['size'],
                    'size_formatted' => Uploader::formatBytes($fileInfo['size']),
                    'mime_type' => $fileInfo['mime_type'],
                    'extension' => $fileInfo['extension'],
                    'folder_id' => $folderId
                ]
            ]
        ], 201);
    }
}
