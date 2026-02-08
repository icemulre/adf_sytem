<?php
$dsn = 'mysql:host=localhost;dbname=adf_narayana_hotel';
try {
    $pdo = new PDO($dsn, 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS `transaction_attachments` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `transaction_type` varchar(50) NOT NULL,
      `transaction_id` int(11) NOT NULL,
      `file_path` varchar(255) NOT NULL,
      `file_name` varchar(255) DEFAULT NULL,
      `file_type` varchar(50) DEFAULT NULL,
      `uploaded_by` int(11) DEFAULT NULL,
      `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `idx_trans_type_id` (`transaction_type`,`transaction_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($sql);
    echo "Table created.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
