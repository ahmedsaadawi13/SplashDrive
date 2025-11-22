<?php
// FILE: /app/models/ApiKey.php

/**
 * ApiKey Model
 * Handles API key management for tenant authentication
 */
class ApiKey extends Model {
    protected $table = 'api_keys';

    /**
     * Generate API key
     *
     * @return string
     */
    private function generateKey() {
        return 'sk_' . bin2hex(random_bytes(32));
    }

    /**
     * Create API key
     *
     * @param array $data
     * @return int API Key ID
     */
    public function createKey($data) {
        if (!isset($data['api_key'])) {
            $data['api_key'] = $this->generateKey();
        }

        return $this->insert($data);
    }

    /**
     * Find by API key
     *
     * @param string $apiKey
     * @return array|false
     */
    public function findByKey($apiKey) {
        $sql = "SELECT * FROM {$this->table} WHERE api_key = :api_key AND is_active = 1 LIMIT 1";
        return $this->fetch($sql, ['api_key' => $apiKey]);
    }

    /**
     * Verify API key and get tenant
     *
     * @param string $apiKey
     * @return array|false Tenant data or false
     */
    public function verifyKey($apiKey) {
        $sql = "SELECT ak.*, t.id as tenant_id, t.name as tenant_name, t.status as tenant_status
                FROM {$this->table} ak
                LEFT JOIN tenants t ON ak.tenant_id = t.id
                WHERE ak.api_key = :api_key AND ak.is_active = 1
                LIMIT 1";
        return $this->fetch($sql, ['api_key' => $apiKey]);
    }

    /**
     * Get API keys by tenant
     *
     * @param int $tenantId
     * @return array
     */
    public function getByTenant($tenantId) {
        $sql = "SELECT ak.*, u.name as created_by_name
                FROM {$this->table} ak
                LEFT JOIN users u ON ak.created_by = u.id
                WHERE ak.tenant_id = :tenant_id
                ORDER BY ak.created_at DESC";
        return $this->fetchAll($sql, ['tenant_id' => $tenantId]);
    }

    /**
     * Update last used time
     *
     * @param int $keyId
     * @return bool
     */
    public function updateLastUsed($keyId) {
        $sql = "UPDATE {$this->table} SET last_used_at = NOW() WHERE id = :id";
        $this->query($sql, ['id' => $keyId]);
        return true;
    }

    /**
     * Deactivate API key
     *
     * @param int $keyId
     * @return bool
     */
    public function deactivate($keyId) {
        return $this->update($keyId, ['is_active' => 0]);
    }
}
