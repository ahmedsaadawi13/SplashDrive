<?php
// FILE: /app/core/Validator.php

/**
 * Validator Class
 * Handles input validation
 */
class Validator {
    private $errors = [];
    private $data = [];

    /**
     * Constructor
     *
     * @param array $data Data to validate
     */
    public function __construct($data = []) {
        $this->data = $data;
    }

    /**
     * Validate required field
     *
     * @param string $field
     * @param string $message
     * @return self
     */
    public function required($field, $message = null) {
        if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
            $this->errors[$field][] = $message ?: "$field is required";
        }
        return $this;
    }

    /**
     * Validate email
     *
     * @param string $field
     * @param string $message
     * @return self
     */
    public function email($field, $message = null) {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = $message ?: "$field must be a valid email";
        }
        return $this;
    }

    /**
     * Validate minimum length
     *
     * @param string $field
     * @param int $length
     * @param string $message
     * @return self
     */
    public function min($field, $length, $message = null) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $length) {
            $this->errors[$field][] = $message ?: "$field must be at least $length characters";
        }
        return $this;
    }

    /**
     * Validate maximum length
     *
     * @param string $field
     * @param int $length
     * @param string $message
     * @return self
     */
    public function max($field, $length, $message = null) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $length) {
            $this->errors[$field][] = $message ?: "$field must not exceed $length characters";
        }
        return $this;
    }

    /**
     * Validate field matches another field
     *
     * @param string $field
     * @param string $matchField
     * @param string $message
     * @return self
     */
    public function matches($field, $matchField, $message = null) {
        if (isset($this->data[$field]) && isset($this->data[$matchField]) &&
            $this->data[$field] !== $this->data[$matchField]) {
            $this->errors[$field][] = $message ?: "$field must match $matchField";
        }
        return $this;
    }

    /**
     * Validate numeric value
     *
     * @param string $field
     * @param string $message
     * @return self
     */
    public function numeric($field, $message = null) {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field][] = $message ?: "$field must be numeric";
        }
        return $this;
    }

    /**
     * Validate value is in array
     *
     * @param string $field
     * @param array $values
     * @param string $message
     * @return self
     */
    public function in($field, $values, $message = null) {
        if (isset($this->data[$field]) && !in_array($this->data[$field], $values)) {
            $this->errors[$field][] = $message ?: "$field has invalid value";
        }
        return $this;
    }

    /**
     * Custom validation rule
     *
     * @param string $field
     * @param callable $callback
     * @param string $message
     * @return self
     */
    public function custom($field, $callback, $message = null) {
        if (isset($this->data[$field]) && !$callback($this->data[$field])) {
            $this->errors[$field][] = $message ?: "$field is invalid";
        }
        return $this;
    }

    /**
     * Check if validation passed
     *
     * @return bool
     */
    public function passes() {
        return empty($this->errors);
    }

    /**
     * Check if validation failed
     *
     * @return bool
     */
    public function fails() {
        return !$this->passes();
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
     * @param string $field
     * @return string|null
     */
    public function getFirstError($field = null) {
        if ($field) {
            return isset($this->errors[$field]) ? $this->errors[$field][0] : null;
        }

        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0];
        }

        return null;
    }

    /**
     * Sanitize string
     *
     * @param string $value
     * @return string
     */
    public static function sanitize($value) {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize array of values
     *
     * @param array $data
     * @return array
     */
    public static function sanitizeArray($data) {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = self::sanitizeArray($value);
            } else {
                $sanitized[$key] = self::sanitize($value);
            }
        }
        return $sanitized;
    }
}
