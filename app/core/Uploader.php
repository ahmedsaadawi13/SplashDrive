<?php
// FILE: /app/core/Uploader.php

/**
 * Uploader Class
 * Handles file uploads securely
 */
class Uploader {
    private $file;
    private $errors = [];
    private $maxSize;
    private $allowedTypes;

    /**
     * Constructor
     *
     * @param array $file $_FILES array element
     */
    public function __construct($file) {
        $this->file = $file;
        $this->maxSize = MAX_UPLOAD_SIZE;
        $this->allowedTypes = explode(',', ALLOWED_FILE_TYPES);
    }

    /**
     * Set maximum file size
     *
     * @param int $size Size in bytes
     * @return self
     */
    public function setMaxSize($size) {
        $this->maxSize = $size;
        return $this;
    }

    /**
     * Set allowed file types
     *
     * @param array $types Array of extensions
     * @return self
     */
    public function setAllowedTypes($types) {
        $this->allowedTypes = $types;
        return $this;
    }

    /**
     * Validate uploaded file
     *
     * @return bool
     */
    public function validate() {
        $this->errors = [];

        // Check if file was uploaded
        if (!isset($this->file['tmp_name']) || !is_uploaded_file($this->file['tmp_name'])) {
            $this->errors[] = 'No file uploaded';
            return false;
        }

        // Check for upload errors
        if ($this->file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadErrorMessage($this->file['error']);
            return false;
        }

        // Check file size
        if ($this->file['size'] > $this->maxSize) {
            $maxSizeMB = round($this->maxSize / 1024 / 1024, 2);
            $this->errors[] = "File size exceeds maximum allowed size of {$maxSizeMB}MB";
            return false;
        }

        // Check file extension
        $extension = strtolower(pathinfo($this->file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedTypes)) {
            $this->errors[] = "File type not allowed. Allowed types: " . implode(', ', $this->allowedTypes);
            return false;
        }

        // Check for valid file content (prevent file type spoofing)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $this->file['tmp_name']);
        finfo_close($finfo);

        // Validate mime type matches extension (basic check)
        if (!$this->isValidMimeType($mimeType, $extension)) {
            $this->errors[] = "File content does not match file extension";
            return false;
        }

        return true;
    }

    /**
     * Upload file to destination
     *
     * @param string $destination Directory path
     * @param string $filename Optional custom filename
     * @return string|false Stored filename or false on failure
     */
    public function upload($destination, $filename = null) {
        if (!$this->validate()) {
            return false;
        }

        // Create destination directory if it doesn't exist
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        // Generate unique filename if not provided
        if ($filename === null) {
            $extension = strtolower(pathinfo($this->file['name'], PATHINFO_EXTENSION));
            $filename = $this->generateUniqueFilename($extension);
        }

        // Sanitize filename to prevent directory traversal
        $filename = $this->sanitizeFilename($filename);

        $filepath = $destination . '/' . $filename;

        // Move uploaded file
        if (move_uploaded_file($this->file['tmp_name'], $filepath)) {
            // Set proper permissions
            chmod($filepath, 0644);
            return $filename;
        }

        $this->errors[] = 'Failed to move uploaded file';
        return false;
    }

    /**
     * Generate unique filename
     *
     * @param string $extension
     * @return string
     */
    private function generateUniqueFilename($extension) {
        return uniqid('file_', true) . '_' . time() . '.' . $extension;
    }

    /**
     * Sanitize filename
     *
     * @param string $filename
     * @return string
     */
    private function sanitizeFilename($filename) {
        // Remove any path components
        $filename = basename($filename);

        // Remove any characters that aren't alphanumeric, underscore, dash, or dot
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);

        return $filename;
    }

    /**
     * Get upload error message
     *
     * @param int $errorCode
     * @return string
     */
    private function getUploadErrorMessage($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive in HTML form',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];

        return isset($errors[$errorCode]) ? $errors[$errorCode] : 'Unknown upload error';
    }

    /**
     * Validate mime type matches extension
     *
     * @param string $mimeType
     * @param string $extension
     * @return bool
     */
    private function isValidMimeType($mimeType, $extension) {
        $mimeMap = [
            'jpg' => ['image/jpeg', 'image/jpg'],
            'jpeg' => ['image/jpeg', 'image/jpg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'ppt' => ['application/vnd.ms-powerpoint'],
            'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
            'txt' => ['text/plain'],
            'zip' => ['application/zip', 'application/x-zip-compressed'],
            'rar' => ['application/x-rar-compressed', 'application/octet-stream']
        ];

        if (!isset($mimeMap[$extension])) {
            // Unknown extension, allow it (can be enhanced)
            return true;
        }

        return in_array($mimeType, $mimeMap[$extension]);
    }

    /**
     * Get errors
     *
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Get first error
     *
     * @return string|null
     */
    public function getFirstError() {
        return !empty($this->errors) ? $this->errors[0] : null;
    }

    /**
     * Get file info
     *
     * @return array
     */
    public function getFileInfo() {
        return [
            'original_name' => $this->file['name'],
            'size' => $this->file['size'],
            'mime_type' => $this->file['type'],
            'extension' => strtolower(pathinfo($this->file['name'], PATHINFO_EXTENSION))
        ];
    }

    /**
     * Format bytes to human readable size
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    public static function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
