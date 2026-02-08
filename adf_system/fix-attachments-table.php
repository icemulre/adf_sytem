<?php
// fix-attachments-table.php
require_once 'config/config.php';
require_once 'config/database.php';

// Force localhost for this script if needed, or rely on config
$_SERVER['HTTP_HOST'] = 'localhost'; 

$db = Database::getInstance();
// We need to switch to adf_narayana_hotel
Database::switchDatabase('adf_narayana_hotel');

$sql = "CREATE TABLE IF NOT EXISTS `transaction_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_type` varchar(50) NOT NULL COMMENT 'purchase_order, expense, etc',
  `transaction_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_trans_type_id` (`transaction_type`,`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

try {
    $db->getConnection()->exec($sql);
    echo "Table transaction_attachments created successfully in adf_narayana_hotel\n";
} catch (Exception $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
