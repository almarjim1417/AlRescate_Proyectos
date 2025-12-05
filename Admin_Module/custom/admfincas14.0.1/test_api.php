<?php
// Test script for admfincas module
// This script tests the module without needing web authentication

// Prevent Dolibarr from redirecting to login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load Dolibarr environment
define('NOREQUIREDB', 1);
define('NOREQUIREUSER', 1);
define('NOREQUIREMENU', 1);

$res = include __DIR__ . '/../../main.inc.php';
if (!$res) {
    die("Error: Unable to load Dolibarr environment\n");
}

// Load Admfinca class
require_once __DIR__ . '/class/admfinca.class.php';

// Create a mock user object for testing
$user = new User($db);
$user->id = 1;
$user->login = 'admin';

echo "=== Admfincas Module Test ===\n\n";

// Test 1: Database connection
echo "1. Testing database connection...\n";
try {
    $result = $db->query("SELECT COUNT(*) as cnt FROM " . MAIN_DB_PREFIX . "admfincas_admfinca");
    if ($result) {
        $obj = $db->fetch_object($result);
        echo "   ✓ Database OK: {$obj->cnt} records found\n\n";
    }
} catch (Exception $e) {
    echo "   ✗ Database Error: " . $e->getMessage() . "\n\n";
}

// Test 2: Class instantiation
echo "2. Testing Admfinca class instantiation...\n";
try {
    $admfinca = new Admfinca($db);
    echo "   ✓ Class instantiated successfully\n\n";
} catch (Exception $e) {
    echo "   ✗ Class Error: " . $e->getMessage() . "\n\n";
}

// Test 3: Test CREATE method
echo "3. Testing CREATE method...\n";
try {
    $admfinca = new Admfinca($db);
    $admfinca->ref = 'TEST-' . time();
    $admfinca->label = 'Test Admin ' . date('Y-m-d H:i:s');
    $admfinca->name = 'Test Name';
    $admfinca->address = 'Test Address 123';
    $admfinca->zip = '28001';
    $admfinca->town = 'Test Town';
    $admfinca->fk_country = 61;
    $admfinca->email = 'test@example.com';
    $admfinca->phone = '123456789';
    $admfinca->status = 1;
    
    $result = $admfinca->create($user);
    
    if ($result > 0) {
        echo "   ✓ CREATE successful! New ID: {$result}\n";
        echo "   ✓ Ref: {$admfinca->ref}\n\n";
    } else {
        echo "   ✗ CREATE failed: {$admfinca->error}\n\n";
    }
} catch (Exception $e) {
    echo "   ✗ CREATE Error: " . $e->getMessage() . "\n";
    echo "   Stack: " . $e->getTraceAsString() . "\n\n";
}

// Test 4: Test FETCH method
echo "4. Testing FETCH method...\n";
try {
    $admfinca = new Admfinca($db);
    $result = $admfinca->fetch($admfinca->id ?? 1);
    
    if ($result > 0) {
        echo "   ✓ FETCH successful! Record: {$admfinca->ref}\n\n";
    } else {
        echo "   ✗ FETCH failed: {$admfinca->error}\n\n";
    }
} catch (Exception $e) {
    echo "   ✗ FETCH Error: " . $e->getMessage() . "\n\n";
}

// Test 5: Test UPDATE method
echo "5. Testing UPDATE method...\n";
try {
    if (isset($admfinca->id) && $admfinca->id > 0) {
        $_POST['portal_user'] = 'testuser';
        $_POST['portal_pass'] = 'testpass123';
        
        $result = $admfinca->update($user);
        
        if ($result > 0) {
            echo "   ✓ UPDATE successful!\n\n";
        } else {
            echo "   ✗ UPDATE failed: {$admfinca->error}\n\n";
        }
    } else {
        echo "   ⊘ UPDATE skipped (no record ID)\n\n";
    }
} catch (Exception $e) {
    echo "   ✗ UPDATE Error: " . $e->getMessage() . "\n\n";
}

// Test 6: List all records
echo "6. Testing LIST (all records)...\n";
try {
    $sql = "SELECT rowid, ref, label FROM " . MAIN_DB_PREFIX . "admfincas_admfinca ORDER BY rowid DESC LIMIT 5";
    $result = $db->query($sql);
    
    if ($result) {
        $count = 0;
        while ($obj = $db->fetch_object($result)) {
            echo "   • ID: {$obj->rowid}, Ref: {$obj->ref}, Label: {$obj->label}\n";
            $count++;
        }
        echo "   ✓ Total records retrieved: {$count}\n\n";
    }
} catch (Exception $e) {
    echo "   ✗ LIST Error: " . $e->getMessage() . "\n\n";
}

echo "=== Test Complete ===\n";
?>
