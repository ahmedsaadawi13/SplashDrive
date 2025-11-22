<?php
// FILE: /tests/basic_test.php

/**
 * Basic Test Script for SplashDrive
 * Tests fundamental functionality
 */

require_once __DIR__ . '/../config/config.php';

echo "===========================================\n";
echo "   SplashDrive Basic Test Suite\n";
echo "===========================================\n\n";

$passed = 0;
$failed = 0;

// Test 1: Database Connection
echo "Test 1: Database Connection... ";
try {
    $db = Database::getInstance();
    $db->getConnection();
    echo "✓ PASSED\n";
    $passed++;
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 2: Configuration Loading
echo "Test 2: Configuration Loading... ";
if (defined('APP_NAME') && defined('DB_HOST') && defined('DB_NAME')) {
    echo "✓ PASSED\n";
    $passed++;
} else {
    echo "✗ FAILED: Configuration constants not defined\n";
    $failed++;
}

// Test 3: Upload Directory
echo "Test 3: Upload Directory Writable... ";
if (is_dir(UPLOAD_PATH) && is_writable(UPLOAD_PATH)) {
    echo "✓ PASSED\n";
    $passed++;
} else {
    echo "✗ FAILED: Upload directory not writable\n";
    $failed++;
}

// Test 4: Core Classes Exist
echo "Test 4: Core Classes Exist... ";
$coreClasses = ['Database', 'Model', 'Controller', 'Router', 'Session', 'Auth', 'Validator', 'Uploader'];
$allExist = true;
foreach ($coreClasses as $class) {
    if (!class_exists($class)) {
        echo "✗ FAILED: Class $class not found\n";
        $allExist = false;
        break;
    }
}
if ($allExist) {
    echo "✓ PASSED\n";
    $passed++;
} else {
    $failed++;
}

// Test 5: Database Tables Exist
echo "Test 5: Database Tables Exist... ";
try {
    $db = Database::getInstance();
    $tables = ['tenants', 'users', 'files', 'folders', 'subscription_plans', 'tenant_subscriptions',
               'invoices', 'payments', 'activity_logs', 'api_keys', 'file_shares', 'notifications'];

    $allTablesExist = true;
    foreach ($tables as $table) {
        $result = $db->fetch("SHOW TABLES LIKE '$table'");
        if (!$result) {
            echo "✗ FAILED: Table $table not found\n";
            $allTablesExist = false;
            break;
        }
    }

    if ($allTablesExist) {
        echo "✓ PASSED\n";
        $passed++;
    } else {
        $failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 6: Seed Data Exists
echo "Test 6: Seed Data Exists... ";
try {
    $db = Database::getInstance();
    $userCount = $db->fetch("SELECT COUNT(*) as count FROM users");
    $tenantCount = $db->fetch("SELECT COUNT(*) as count FROM tenants");
    $planCount = $db->fetch("SELECT COUNT(*) as count FROM subscription_plans");

    if ($userCount['count'] > 0 && $tenantCount['count'] > 0 && $planCount['count'] > 0) {
        echo "✓ PASSED\n";
        $passed++;
    } else {
        echo "✗ FAILED: No seed data found\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 7: Model Functionality
echo "Test 7: Model Functionality... ";
try {
    require_once APP_PATH . '/models/User.php';
    $userModel = new User();
    $users = $userModel->findAll([], '', 1);

    if (is_array($users)) {
        echo "✓ PASSED\n";
        $passed++;
    } else {
        echo "✗ FAILED: Model query returned unexpected result\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 8: Password Hashing
echo "Test 8: Password Hashing... ";
$testPassword = 'testpassword123';
$hash = password_hash($testPassword, PASSWORD_DEFAULT);
if (password_verify($testPassword, $hash)) {
    echo "✓ PASSED\n";
    $passed++;
} else {
    echo "✗ FAILED: Password hashing verification failed\n";
    $failed++;
}

// Test 9: Session Functionality
echo "Test 9: Session Functionality... ";
Session::set('test_key', 'test_value');
if (Session::get('test_key') === 'test_value') {
    Session::remove('test_key');
    echo "✓ PASSED\n";
    $passed++;
} else {
    echo "✗ FAILED: Session get/set failed\n";
    $failed++;
}

// Test 10: CSRF Token Generation
echo "Test 10: CSRF Token Generation... ";
$token = Session::getCsrfToken();
if (strlen($token) === 64) {
    echo "✓ PASSED\n";
    $passed++;
} else {
    echo "✗ FAILED: Invalid CSRF token\n";
    $failed++;
}

// Summary
echo "\n===========================================\n";
echo "   Test Summary\n";
echo "===========================================\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "Total:  " . ($passed + $failed) . "\n";
echo "===========================================\n";

if ($failed === 0) {
    echo "\n✓ All tests passed!\n\n";
    exit(0);
} else {
    echo "\n✗ Some tests failed. Please check the errors above.\n\n";
    exit(1);
}
