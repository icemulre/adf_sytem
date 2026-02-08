<?php
require_once 'config/config.php';
require_once 'config/database.php';

$db = Database::getInstance();

try {
    // 1. Change payment_method to VARCHAR(50) to prevent truncation errors
    $db->query("ALTER TABLE sales_invoices_header MODIFY COLUMN payment_method VARCHAR(50) NOT NULL DEFAULT 'cash'");
    echo "Successfully changed payment_method to VARCHAR(50).\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
