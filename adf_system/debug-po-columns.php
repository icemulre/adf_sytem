<?php
require_once 'config/config.php';
require_once 'config/database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

try {
    $log = fopen('debug_po_log.txt', 'w');
    fwrite($log, "--- CHECKING purchase_orders_header COLUMNS ---\n");
    $stmt = $conn->query("SHOW COLUMNS FROM purchase_orders_header");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        fwrite($log, $col['Field'] . " (" . $col['Type'] . ")\n");
    }
    fclose($log);
    echo "Log generated.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>