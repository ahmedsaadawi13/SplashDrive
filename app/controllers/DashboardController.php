<?php
// FILE: /app/controllers/DashboardController.php

/**
 * DashboardController
 * Handles dashboard views for users and admins
 */
class DashboardController extends Controller {
    public function __construct() {
        Auth::require();
    }

    /**
     * Main dashboard
     */
    public function index() {
        $tenantId = Auth::tenantId();

        // Platform admin dashboard
        if (Auth::isPlatformAdmin()) {
            $this->admin();
            return;
        }

        // Get tenant with subscription
        $tenantModel = $this->model('Tenant');
        $tenant = $tenantModel->getWithSubscription($tenantId);

        // Get statistics
        $stats = $tenantModel->getStatistics($tenantId);

        // Get recent files
        $fileModel = $this->model('File');
        $recentFiles = $fileModel->getRecent($tenantId, 10);

        // Get recent activity
        $activityModel = $this->model('ActivityLog');
        $recentActivity = $activityModel->getRecent($tenantId, 10);

        // Calculate storage percentage
        $storagePercentage = 0;
        if ($tenant['max_storage_bytes'] > 0) {
            $storagePercentage = ($stats['storage_used'] / $tenant['max_storage_bytes']) * 100;
        }

        // Check for warnings
        $warnings = [];
        if ($storagePercentage >= 90) {
            $warnings[] = 'You have used ' . round($storagePercentage) . '% of your storage. Please upgrade your plan or delete some files.';
        } elseif ($storagePercentage >= 80) {
            $warnings[] = 'You have used ' . round($storagePercentage) . '% of your storage.';
        }

        // Check user limit
        if ($stats['user_count'] >= $tenant['max_users']) {
            $warnings[] = 'You have reached your user limit. Please upgrade your plan to add more users.';
        }

        $this->view('dashboard/index', [
            'title' => 'Dashboard - ' . APP_NAME,
            'tenant' => $tenant,
            'stats' => $stats,
            'recentFiles' => $recentFiles,
            'recentActivity' => $recentActivity,
            'storagePercentage' => $storagePercentage,
            'warnings' => $warnings
        ]);
    }

    /**
     * Platform admin dashboard
     */
    public function admin() {
        Auth::requireRole(ROLE_PLATFORM_ADMIN);

        $tenantModel = $this->model('Tenant');
        $userModel = $this->model('User');

        // Get all tenants with stats
        $tenants = $tenantModel->getAllWithStats(10, 0);

        // Get total statistics
        $totalTenants = $tenantModel->count();
        $totalUsers = $userModel->count();

        // Calculate total storage used
        $totalStorageQuery = "SELECT COALESCE(SUM(file_size), 0) as total FROM files WHERE is_deleted = 0";
        $totalStorageResult = $tenantModel->fetch($totalStorageQuery);
        $totalStorage = $totalStorageResult['total'];

        // Get recent activities (platform-wide)
        $activityModel = $this->model('ActivityLog');
        $sql = "SELECT al.*, u.name as user_name, t.name as tenant_name
                FROM activity_logs al
                LEFT JOIN users u ON al.user_id = u.id
                LEFT JOIN tenants t ON al.tenant_id = t.id
                ORDER BY al.created_at DESC
                LIMIT 20";
        $recentActivity = $activityModel->fetchAll($sql);

        $this->view('dashboard/admin', [
            'title' => 'Platform Admin Dashboard - ' . APP_NAME,
            'tenants' => $tenants,
            'totalTenants' => $totalTenants,
            'totalUsers' => $totalUsers,
            'totalStorage' => $totalStorage,
            'recentActivity' => $recentActivity
        ]);
    }
}
