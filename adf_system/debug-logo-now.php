<?php
require_once 'config/config.php';
require_once 'config/database.php';

$db = Database::getInstance();

echo "<h1>DEBUG LOGO - SIMPLE CHECK</h1>";
echo "<hr>";

// Get business ID
$businessId = ACTIVE_BUSINESS_ID;
echo "<h2>1. Active Business ID: <span style='color:red'>$businessId</span></h2>";

// Check invoice logo key
$invoiceLogoKey = 'invoice_logo_' . $businessId;
echo "<h2>2. Looking for key: <span style='color:red'>$invoiceLogoKey</span></h2>";

// Get from database
$result = $db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = ?", [$invoiceLogoKey]);
echo "<h2>3. Database Result:</h2>";
if ($result) {
    $filename = $result['setting_value'];
    echo "<p style='color:green; font-size:20px;'>✓ FOUND: <b>$filename</b></p>";
    
    // Check file existence
    echo "<h2>4. Checking File:</h2>";
    $path1 = __DIR__ . '/uploads/logos/' . $filename;
    $path2 = $_SERVER['DOCUMENT_ROOT'] . '/adf_system/uploads/logos/' . $filename;
    
    echo "<p><b>Path 1:</b> $path1</p>";
    if (file_exists($path1)) {
        echo "<p style='color:green;'>✓ FILE EXISTS!</p>";
        echo "<p><b>URL should be:</b> " . BASE_URL . "/uploads/logos/$filename</p>";
        echo "<p><img src='" . BASE_URL . "/uploads/logos/$filename' style='max-width:200px; border:2px solid green;' /></p>";
    } else {
        echo "<p style='color:red;'>✗ NOT FOUND</p>";
    }
    
    echo "<p><b>Path 2:</b> $path2</p>";
    if (file_exists($path2)) {
        echo "<p style='color:green;'>✓ FILE EXISTS!</p>";
    } else {
        echo "<p style='color:red;'>✗ NOT FOUND</p>";
    }
    
} else {
    echo "<p style='color:red; font-size:20px;'>✗ NOT FOUND IN DATABASE!</p>";
    
    // Show what keys exist
    echo "<h2>Available invoice_logo keys:</h2>";
    $all = $db->fetchAll("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'invoice_logo%'", []);
    if ($all) {
        echo "<ul>";
        foreach ($all as $row) {
            echo "<li><b>{$row['setting_key']}</b> = {$row['setting_value']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No invoice_logo keys found!</p>";
    }
}

// Show report settings
echo "<hr><h2>5. Report Settings:</h2>";
$reportSettings = ['report_show_logo', 'report_show_address', 'report_show_phone'];
foreach ($reportSettings as $key) {
    $val = $db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
    $value = $val ? $val['setting_value'] : 'not set';
    echo "<p><b>$key:</b> $value</p>";
}

// Show company settings
echo "<hr><h2>6. Company Settings:</h2>";
$companySettings = ['company_name', 'company_tagline', 'company_address', 'company_phone', 'company_email', 'company_logo'];
foreach ($companySettings as $key) {
    $val = $db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
    $value = $val ? $val['setting_value'] : 'not set';
    echo "<p><b>$key:</b> $value</p>";
}
?>
