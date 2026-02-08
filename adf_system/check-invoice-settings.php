<?php
require_once 'config/config.php';
require_once 'includes/Database.php';

// Get active business ID
$businessId = defined('ACTIVE_BUSINESS_ID') ? ACTIVE_BUSINESS_ID : null;

if (!$businessId) {
    die("ACTIVE_BUSINESS_ID not defined");
}

$db = new Database();

echo "<h2>Invoice Settings Check</h2>";
echo "<p><strong>Active Business ID:</strong> $businessId</p>";
echo "<hr>";

// Get all settings
$allSettings = $db->fetchAll("SELECT setting_key, setting_value FROM settings ORDER BY setting_key", []);

echo "<h3>All Settings in Database:</h3>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>Setting Key</th><th>Setting Value</th></tr>";

$invoiceLogoKey = 'invoice_logo_' . $businessId;
$relevantKeys = [
    'company_name',
    'company_tagline',
    'company_address',
    'company_phone',
    'company_email',
    'company_logo',
    $invoiceLogoKey,
    'report_show_logo',
    'report_show_address',
    'report_show_phone'
];

foreach ($allSettings as $setting) {
    $highlight = in_array($setting['setting_key'], $relevantKeys) ? "background: yellow;" : "";
    echo "<tr style='$highlight'>";
    echo "<td>" . htmlspecialchars($setting['setting_key']) . "</td>";
    echo "<td>" . htmlspecialchars($setting['setting_value']) . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<h3>Invoice Logo Key Being Used:</h3>";
echo "<p><code>$invoiceLogoKey</code></p>";

// Check if invoice logo file exists
$settings = [];
foreach ($allSettings as $setting) {
    $settings[$setting['setting_key']] = $setting['setting_value'];
}

$invoiceLogo = $settings[$invoiceLogoKey] ?? null;
echo "<p><strong>Invoice Logo Value:</strong> " . ($invoiceLogo ? htmlspecialchars($invoiceLogo) : "(not set)") . "</p>";

if ($invoiceLogo) {
    $possiblePaths = [
        $_SERVER['DOCUMENT_ROOT'] . '/adf_system/uploads/logos/' . $invoiceLogo,
        dirname(__FILE__) . '/uploads/logos/' . $invoiceLogo,
        $_SERVER['DOCUMENT_ROOT'] . '/adf_system/uploads/' . $invoiceLogo,
        dirname(__FILE__) . '/uploads/' . $invoiceLogo,
    ];
    
    echo "<h4>Checking file existence:</h4>";
    foreach ($possiblePaths as $path) {
        $exists = file_exists($path) ? "✓ EXISTS" : "✗ NOT FOUND";
        echo "<p>$exists: <code>" . htmlspecialchars($path) . "</code></p>";
    }
}

echo "<hr>";
echo "<h3>Constants from config.php:</h3>";
echo "<p><strong>BUSINESS_NAME:</strong> " . (defined('BUSINESS_NAME') ? BUSINESS_NAME : 'not defined') . "</p>";
echo "<p><strong>BUSINESS_ICON:</strong> " . (defined('BUSINESS_ICON') ? BUSINESS_ICON : 'not defined') . "</p>";
echo "<p><strong>BUSINESS_COLOR:</strong> " . (defined('BUSINESS_COLOR') ? BUSINESS_COLOR : 'not defined') . "</p>";
?>
