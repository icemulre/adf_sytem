<?php
/**
 * ADD payment_method AND reference_no COLUMNS TO cash_book TABLE
 * Fix for "column doesn't exist" error when paying invoices
 */

define('APP_ACCESS', true);
require_once 'config/config.php';
require_once 'config/database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

echo "<h2>🔧 Adding missing columns to cash_book table</h2>";
echo "<hr>";

try {
    // Check current database
    $currentDb = $conn->query("SELECT DATABASE()")->fetchColumn();
    echo "<p><strong>Current Database:</strong> $currentDb</p>";
    
    // Check if column exists
    $checkQuery = "SELECT COLUMN_NAME 
                   FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = ? 
                   AND TABLE_NAME = 'cash_book' 
                   AND COLUMN_NAME = 'payment_method'";
    $stmt = $conn->prepare($checkQuery);
    $stmt->execute([$currentDb]);
    $exists = $stmt->fetch();
    
    if ($exists) {
        echo "<p style='color: orange;'>⚠️ Column 'payment_method' already exists in cash_book table</p>";
    } else {
        // Add the column
        $alterQuery = "ALTER TABLE cash_book 
                       ADD COLUMN payment_method ENUM('cash', 'card', 'bank_transfer', 'qris', 'other') 
                       DEFAULT 'cash' 
                       AFTER amount";
        
        $conn->exec($alterQuery);
        
        echo "<p style='color: green;'>✅ Successfully added payment_method column to cash_book table!</p>";
        echo "<p><strong>Column details:</strong></p>";
        echo "<ul>";
        echo "<li>Type: ENUM('cash', 'card', 'bank_transfer', 'qris', 'other')</li>";
        echo "<li>Default: 'cash'</li>";
        echo "<li>Position: After 'amount' column</li>";
        echo "</ul>";
    }
    
    // Check if reference_no column exists
    $checkRefQuery = "SELECT COLUMN_NAME 
                      FROM INFORMATION_SCHEMA.COLUMNS 
                      WHERE TABLE_SCHEMA = ? 
                      AND TABLE_NAME = 'cash_book' 
                      AND COLUMN_NAME = 'reference_no'";
    $stmtRef = $conn->prepare($checkRefQuery);
    $stmtRef->execute([$currentDb]);
    $refExists = $stmtRef->fetch();
    
    if ($refExists) {
        echo "<p style='color: orange;'>⚠️ Column 'reference_no' already exists in cash_book table</p>";
    } else {
        // Add reference_no column
        $alterRefQuery = "ALTER TABLE cash_book 
                          ADD COLUMN reference_no VARCHAR(100) NULL 
                          AFTER description";
        
        $conn->exec($alterRefQuery);
        
        echo "<p style='color: green;'>✅ Successfully added reference_no column to cash_book table!</p>";
        echo "<p><strong>Column details:</strong></p>";
        echo "<ul>";
        echo "<li>Type: VARCHAR(100)</li>";
        echo "<li>Nullable: YES</li>";
        echo "<li>Position: After 'description' column</li>";
        echo "</ul>";
    }
    
    // Show current cash_book structure
    echo "<h3>Current cash_book table structure:</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Default</th></tr>";
    
    $columnsQuery = "SHOW COLUMNS FROM cash_book";
    $columns = $conn->query($columnsQuery)->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td><strong>{$col['Field']}</strong></td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    echo "<p style='color: green; font-weight: bold;'>🎉 Migration completed successfully!</p>";
    echo "<p><a href='modules/sales/index.php'>← Back to Sales Invoices</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
