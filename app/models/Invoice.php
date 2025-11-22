<?php
// FILE: /app/models/Invoice.php

/**
 * Invoice Model
 * Handles billing invoices
 */
class Invoice extends Model {
    protected $table = 'invoices';

    /**
     * Get invoices by tenant
     *
     * @param int $tenantId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getByTenant($tenantId, $limit = 50, $offset = 0) {
        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = :tenant_id
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Generate invoice number
     *
     * @return string
     */
    public function generateInvoiceNumber() {
        $year = date('Y');
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE invoice_number LIKE :year";
        $result = $this->fetch($sql, ['year' => "INV-$year-%"]);
        $count = (int)$result['count'] + 1;

        return sprintf('INV-%s-%03d', $year, $count);
    }

    /**
     * Create invoice
     *
     * @param array $data
     * @return int Invoice ID
     */
    public function createInvoice($data) {
        if (!isset($data['invoice_number'])) {
            $data['invoice_number'] = $this->generateInvoiceNumber();
        }

        return $this->insert($data);
    }

    /**
     * Mark invoice as paid
     *
     * @param int $invoiceId
     * @return bool
     */
    public function markAsPaid($invoiceId) {
        return $this->update($invoiceId, [
            'status' => 'paid',
            'paid_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get pending invoices
     *
     * @param int $tenantId
     * @return array
     */
    public function getPending($tenantId) {
        return $this->findAll([
            'tenant_id' => $tenantId,
            'status' => 'pending'
        ], 'due_date ASC');
    }
}
