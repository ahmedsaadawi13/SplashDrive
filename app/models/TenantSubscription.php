<?php
// FILE: /app/models/TenantSubscription.php

/**
 * TenantSubscription Model
 * Handles tenant subscription records
 */
class TenantSubscription extends Model {
    protected $table = 'tenant_subscriptions';

    /**
     * Get subscription by tenant ID
     *
     * @param int $tenantId
     * @return array|false
     */
    public function getByTenant($tenantId) {
        $sql = "SELECT ts.*, sp.name as plan_name, sp.price_monthly, sp.price_yearly,
                       sp.max_storage_bytes, sp.max_users, sp.max_files
                FROM {$this->table} ts
                LEFT JOIN subscription_plans sp ON ts.plan_id = sp.id
                WHERE ts.tenant_id = :tenant_id
                ORDER BY ts.created_at DESC
                LIMIT 1";
        return $this->fetch($sql, ['tenant_id' => $tenantId]);
    }

    /**
     * Change subscription plan
     *
     * @param int $tenantId
     * @param int $newPlanId
     * @param string $billingCycle
     * @return int Subscription ID
     */
    public function changePlan($tenantId, $newPlanId, $billingCycle = 'monthly') {
        $current = $this->getByTenant($tenantId);

        if ($current) {
            // Update existing subscription
            $this->update($current['id'], [
                'plan_id' => $newPlanId,
                'billing_cycle' => $billingCycle,
                'status' => 'active',
                'current_period_start' => date('Y-m-d H:i:s'),
                'current_period_end' => $this->calculatePeriodEnd($billingCycle)
            ]);
            return $current['id'];
        } else {
            // Create new subscription
            return $this->insert([
                'tenant_id' => $tenantId,
                'plan_id' => $newPlanId,
                'billing_cycle' => $billingCycle,
                'status' => 'active',
                'current_period_start' => date('Y-m-d H:i:s'),
                'current_period_end' => $this->calculatePeriodEnd($billingCycle)
            ]);
        }
    }

    /**
     * Calculate period end date
     *
     * @param string $billingCycle
     * @return string
     */
    private function calculatePeriodEnd($billingCycle) {
        if ($billingCycle === 'yearly') {
            return date('Y-m-d H:i:s', strtotime('+1 year'));
        }
        return date('Y-m-d H:i:s', strtotime('+1 month'));
    }

    /**
     * Cancel subscription
     *
     * @param int $tenantId
     * @return bool
     */
    public function cancel($tenantId) {
        $subscription = $this->getByTenant($tenantId);
        if (!$subscription) {
            return false;
        }

        $this->update($subscription['id'], [
            'status' => 'cancelled',
            'cancelled_at' => date('Y-m-d H:i:s')
        ]);

        return true;
    }

    /**
     * Get all active subscriptions
     *
     * @return array
     */
    public function getActiveSubscriptions() {
        return $this->findAll(['status' => 'active']);
    }
}
