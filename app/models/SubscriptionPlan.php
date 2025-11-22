<?php
// FILE: /app/models/SubscriptionPlan.php

/**
 * SubscriptionPlan Model
 * Handles subscription plan data
 */
class SubscriptionPlan extends Model {
    protected $table = 'subscription_plans';

    /**
     * Get all active plans
     *
     * @return array
     */
    public function getActivePlans() {
        return $this->findAll(['is_active' => 1], 'sort_order ASC');
    }

    /**
     * Find plan by slug
     *
     * @param string $slug
     * @return array|false
     */
    public function findBySlug($slug) {
        $sql = "SELECT * FROM {$this->table} WHERE slug = :slug LIMIT 1";
        return $this->fetch($sql, ['slug' => $slug]);
    }

    /**
     * Get plan features as array
     *
     * @param int $planId
     * @return array
     */
    public function getFeatures($planId) {
        $plan = $this->find($planId);
        if (!$plan || !$plan['features']) {
            return [];
        }

        return explode(',', $plan['features']);
    }
}
