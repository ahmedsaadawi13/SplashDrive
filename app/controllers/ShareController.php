<?php
// FILE: /app/controllers/ShareController.php

/**
 * ShareController
 * Handles public sharing links
 */
class ShareController extends Controller {
    private $shareModel;
    private $fileModel;
    private $folderModel;
    private $activityModel;

    public function __construct() {
        $this->shareModel = $this->model('FileShare');
        $this->fileModel = $this->model('File');
        $this->folderModel = $this->model('Folder');
        $this->activityModel = $this->model('ActivityLog');
    }

    /**
     * View public share (no auth required)
     */
    public function view($token) {
        // Get share details
        $share = $this->shareModel->getByToken($token);

        if (!$share) {
            $this->view('share/not_found', [
                'title' => 'Share Not Found - ' . APP_NAME
            ]);
            return;
        }

        // Check if expired
        if (!$this->shareModel->isValid($token)) {
            $this->view('share/expired', [
                'title' => 'Share Expired - ' . APP_NAME
            ]);
            return;
        }

        // Increment access count
        $this->shareModel->incrementAccessCount($share['id']);

        // Display based on type
        if ($share['share_type'] === 'file') {
            $this->view('share/view_file', [
                'title' => $share['original_filename'] . ' - ' . APP_NAME,
                'share' => $share,
                'token' => $token
            ], 'layouts/public');
        } else {
            // Folder share
            $this->view('share/view_folder', [
                'title' => $share['folder_name'] . ' - ' . APP_NAME,
                'share' => $share,
                'token' => $token
            ], 'layouts/public');
        }
    }

    /**
     * List shares for current tenant
     */
    public function index() {
        Auth::require();

        $tenantId = Auth::tenantId();
        $shares = $this->shareModel->getByTenant($tenantId);

        $this->view('share/index', [
            'title' => 'Shared Links - ' . APP_NAME,
            'shares' => $shares
        ]);
    }

    /**
     * Create share link
     */
    public function create() {
        Auth::require();

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
        $type = $this->post('type');
        $itemId = $this->post('item_id');
        $expiresIn = $this->post('expires_in');

        // Validate type
        if (!in_array($type, ['file', 'folder'])) {
            $this->json(['success' => false, 'message' => 'Invalid type'], 400);
            return;
        }

        // Validate item
        if ($type === 'file') {
            $item = $this->fileModel->getWithDetails($itemId, $tenantId);
            if (!$item) {
                $this->json(['success' => false, 'message' => 'File not found'], 404);
                return;
            }
        } else {
            $item = $this->folderModel->getWithDetails($itemId, $tenantId);
            if (!$item) {
                $this->json(['success' => false, 'message' => 'Folder not found'], 404);
                return;
            }
        }

        // Calculate expiration
        $expiresAt = null;
        if ($expiresIn && is_numeric($expiresIn)) {
            $expiresAt = date('Y-m-d H:i:s', strtotime('+' . $expiresIn . ' days'));
        }

        // Create share
        $shareData = [
            'tenant_id' => $tenantId,
            'share_type' => $type,
            'permissions' => 'download',
            'expires_at' => $expiresAt,
            'created_by' => $userId
        ];

        if ($type === 'file') {
            $shareData['file_id'] = $itemId;
        } else {
            $shareData['folder_id'] = $itemId;
        }

        $shareId = $this->shareModel->createShare($shareData);
        $share = $this->shareModel->find($shareId);

        // Log activity
        $this->activityModel->log([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'action_type' => ACTION_SHARE_CREATE,
            'target_type' => $type,
            'target_id' => $itemId,
            'description' => 'Created share link for ' . $type
        ]);

        $shareUrl = APP_URL . '/share/' . $share['token'];

        $this->json([
            'success' => true,
            'message' => 'Share link created successfully',
            'share_url' => $shareUrl,
            'token' => $share['token']
        ]);
    }

    /**
     * Delete share link
     */
    public function delete($shareId) {
        Auth::require();

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

        $share = $this->shareModel->find($shareId);

        if (!$share || $share['tenant_id'] != $tenantId) {
            $this->json(['success' => false, 'message' => 'Share not found'], 404);
            return;
        }

        $this->shareModel->delete($shareId);

        // Log activity
        $this->activityModel->log([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'action_type' => ACTION_SHARE_DELETE,
            'target_type' => $share['share_type'],
            'target_id' => $share['file_id'] ?: $share['folder_id'],
            'description' => 'Deleted share link'
        ]);

        $this->json([
            'success' => true,
            'message' => 'Share link deleted successfully'
        ]);
    }
}
