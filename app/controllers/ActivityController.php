<?php
// FILE: /app/controllers/ActivityController.php

/**
 * ActivityController
 * Handles activity log viewing
 */
class ActivityController extends Controller {
    private $activityModel;

    public function __construct() {
        Auth::requirePermission('view_activity_log');
        $this->activityModel = $this->model('ActivityLog');
    }

    /**
     * View activity log
     */
    public function index() {
        $tenantId = Auth::tenantId();

        // Get filters
        $filters = [];
        if ($this->get('user_id')) {
            $filters['user_id'] = $this->get('user_id');
        }
        if ($this->get('action_type')) {
            $filters['action_type'] = $this->get('action_type');
        }
        if ($this->get('date_from')) {
            $filters['date_from'] = $this->get('date_from');
        }
        if ($this->get('date_to')) {
            $filters['date_to'] = $this->get('date_to');
        }

        $page = (int)$this->get('page', 1);
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $activities = $this->activityModel->getByTenant($tenantId, $filters, $limit, $offset);
        $totalActivities = $this->activityModel->countByTenant($tenantId, $filters);

        // Get all users for filter dropdown
        $userModel = $this->model('User');
        $users = $userModel->getByTenant($tenantId, 1000, 0);

        $this->view('activity/index', [
            'title' => 'Activity Log - ' . APP_NAME,
            'activities' => $activities,
            'users' => $users,
            'filters' => $filters,
            'totalActivities' => $totalActivities,
            'currentPage' => $page
        ]);
    }

    /**
     * Export activity log
     */
    public function export() {
        $tenantId = Auth::tenantId();

        // Get all activities
        $activities = $this->activityModel->getByTenant($tenantId, [], 10000, 0);

        // Generate CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="activity_log_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // CSV headers
        fputcsv($output, ['Date', 'User', 'Action', 'Target Type', 'Description', 'IP Address']);

        // CSV rows
        foreach ($activities as $activity) {
            fputcsv($output, [
                $activity['created_at'],
                $activity['user_name'] ?? 'System',
                $activity['action_type'],
                $activity['target_type'] ?? '-',
                $activity['description'],
                $activity['ip_address'] ?? '-'
            ]);
        }

        fclose($output);
        exit;
    }
}
