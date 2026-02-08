<?php
require_once 'config/database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query('DESCRIBE investors');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
