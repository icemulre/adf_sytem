<?php
require_once 'config/config.php';
require_once 'config/database.php';

$db = Database::getInstance();

// Insert default report settings if not exist
$settings = [
    'report_show_logo' => '1',
    'report_show_address' => '1',
    'report_show_phone' => '1'
];

echo "<h1>Inserting Report Settings...</h1>";

foreach ($settings as $key => $value) {
    // Check if exists
    $exists = $db->fetchOne("SELECT COUNT(*) as count FROM settings WHERE setting_key = ?", [$key]);
    
    if ($exists['count'] == 0) {
        // Insert
        $db->insert('settings', [
            'setting_key' => $key,
            'setting_value' => $value
        ]);
        echo "<p style='color:green;'>✓ Inserted: <b>$key</b> = $value</p>";
    } else {
        // Update to ensure it's '1'
        $db->update('settings', 
            ['setting_value' => $value], 
            'setting_key = ?', 
            [$key]
        );
        echo "<p style='color:blue;'>✓ Updated: <b>$key</b> = $value</p>";
    }
}

echo "<hr>";
echo "<h2>Done! Settings are now active.</h2>";
echo "<p><a href='modules/sales/view-invoice.php?id=7' style='font-size:20px;'>→ Test Invoice Now</a></p>";
?>
