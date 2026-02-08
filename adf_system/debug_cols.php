<?php
$_SERVER['HTTP_HOST'] = 'localhost';
// Suppress warnings
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
require_once 'config/database.php';
$db = Database::getInstance();
// Switch to correct DB
Database::switchDatabase('adf_narayana_hotel');

try {
    echo "--- cash_book structure ---\n";
    $cols = $db->fetchAll('DESCRIBE cash_book');
    foreach ($cols as $col) {
        echo $col['Field'] . "\n";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
