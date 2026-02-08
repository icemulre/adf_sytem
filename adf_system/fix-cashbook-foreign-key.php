<?php
/**
 * FIX FOREIGN KEY CONSTRAINT IN cash_book TABLE
 * Remove created_by foreign key constraint that causes errors
 * in multi-database architecture
 */

define('APP_ACCESS', true);
require_once 'config/config.php';
require_once 'config/database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

echo "<h2>🔧 Fixing Foreign Key Constraints in cash_book table</h2>";
echo "<hr>";

try {
    // Get current database
    $currentDb = $conn->query("SELECT DATABASE()")->fetchColumn();
    echo "<p><strong>Current Database:</strong> $currentDb</p>";
    
    // Get all foreign keys for cash_book table
    echo "<h3>Current Foreign Keys:</h3>";
    $fkQuery = "SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = ?
                AND TABLE_NAME = 'cash_book'
                AND REFERENCED_TABLE_NAME IS NOT NULL";
    
    $stmt = $conn->prepare($fkQuery);
    $stmt->execute([$currentDb]);
    $foreignKeys = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($foreignKeys)) {
        echo "<p>No foreign keys found.</p>";
    } else {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Constraint Name</th><th>Column</th><th>References</th></tr>";
        foreach ($foreignKeys as $fk) {
            echo "<tr>";
            echo "<td>{$fk['CONSTRAINT_NAME']}</td>";
            echo "<td>{$fk['COLUMN_NAME']}</td>";
            echo "<td>{$fk['REFERENCED_TABLE_NAME']}({$fk['REFERENCED_COLUMN_NAME']})</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<h3>Removing problematic foreign key constraints...</h3>";
    
    // Drop foreign key for created_by (users reference)
    $droppedCount = 0;
    
    // Check if cash_book_ibfk_3 exists (created_by reference)
    foreach ($foreignKeys as $fk) {
        if ($fk['CONSTRAINT_NAME'] === 'cash_book_ibfk_3' || 
            ($fk['COLUMN_NAME'] === 'created_by' && $fk['REFERENCED_TABLE_NAME'] === 'users')) {
            
            try {
                $dropQuery = "ALTER TABLE cash_book DROP FOREIGN KEY {$fk['CONSTRAINT_NAME']}";
                $conn->exec($dropQuery);
                echo "<p style='color: green;'>✅ Dropped foreign key: {$fk['CONSTRAINT_NAME']} (created_by → users)</p>";
                $droppedCount++;
            } catch (Exception $e) {
                echo "<p style='color: orange;'>⚠️ Could not drop {$fk['CONSTRAINT_NAME']}: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    // Also try to drop by common names just in case
    $possibleConstraints = ['cash_book_ibfk_3', 'fk_cash_book_created_by', 'fk_created_by'];
    foreach ($possibleConstraints as $constraintName) {
        try {
            $dropQuery = "ALTER TABLE cash_book DROP FOREIGN KEY {$constraintName}";
            $conn->exec($dropQuery);
            echo "<p style='color: green;'>✅ Dropped foreign key: {$constraintName}</p>";
            $droppedCount++;
        } catch (Exception $e) {
            // Silently continue - constraint might not exist
        }
    }
    
    if ($droppedCount === 0) {
        echo "<p style='color: orange;'>ℹ️ No foreign key constraints were dropped (might already be removed)</p>";
    }
    
    // Show remaining foreign keys
    echo "<hr>";
    echo "<h3>Remaining Foreign Keys:</h3>";
    
    $stmt->execute([$currentDb]);
    $remainingFKs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($remainingFKs)) {
        echo "<p>No foreign keys remaining.</p>";
    } else {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Constraint Name</th><th>Column</th><th>References</th></tr>";
        foreach ($remainingFKs as $fk) {
            echo "<tr>";
            echo "<td>{$fk['CONSTRAINT_NAME']}</td>";
            echo "<td>{$fk['COLUMN_NAME']}</td>";
            echo "<td>{$fk['REFERENCED_TABLE_NAME']}({$fk['REFERENCED_COLUMN_NAME']})</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<p style='color: green; font-weight: bold;'>🎉 Foreign key fix completed!</p>";
    echo "<p><strong>Note:</strong> The created_by column will still store user IDs, but without enforcing foreign key constraint.</p>";
    echo "<p><strong>Why?</strong> This system uses multi-database architecture where users are in the master database (adf_system) but cash_book is in business databases.</p>";
    echo "<p><a href='modules/sales/index.php'>← Back to Sales Invoices</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
