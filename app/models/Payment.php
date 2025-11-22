<?php
// FILE: /app/models/Payment.php

/**
 * Payment Model
 * Handles payment records
 */
class Payment extends Model {
    protected $table = 'payments';

    /**
     * Create payment record
     *
     * @param array $data
     * @return int Payment ID
     */
    public function createPayment($data) {
        if (!isset($data['transaction_id'])) {
            $data['transaction_id'] = 'txn_' . uniqid() . time();
        }

        if (!isset($data['paid_at']) && $data['status'] === 'success') {
            $data['paid_at'] = date('Y-m-d H:i:s');
        }

        return $this->insert($data);
    }

    /**
     * Get payments by tenant
     *
     * @param int $tenantId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getByTenant($tenantId, $limit = 50, $offset = 0) {
        $sql = "SELECT p.*, i.invoice_number
                FROM {$this->table} p
                LEFT JOIN invoices i ON p.invoice_id = i.id
                WHERE p.tenant_id = :tenant_id
                ORDER BY p.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get payment by invoice
     *
     * @param int $invoiceId
     * @return array|false
     */
    public function getByInvoice($invoiceId) {
        $sql = "SELECT * FROM {$this->table}
                WHERE invoice_id = :invoice_id
                ORDER BY created_at DESC
                LIMIT 1";
        return $this->fetch($sql, ['invoice_id' => $invoiceId]);
    }
}
