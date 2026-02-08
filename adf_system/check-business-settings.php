<?php
/**
 * CHECK BUSINESS SETTINGS
 * Menampilkan semua setting company di database
 */

define('APP_ACCESS', true);
require_once 'config/config.php';
require_once 'config/database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

echo "<h2>🔍 Business Settings Check</h2>";
echo "<hr>";

try {
    // Get current database
    $currentDb = $conn->query("SELECT DATABASE()")->fetchColumn();
    echo "<p><strong>Current Database:</strong> $currentDb</p>";
    
    // Get all company settings
    echo "<h3>Company Settings:</h3>";
    $settings = $db->fetchAll("SELECT setting_key, setting_value, setting_type 
                               FROM settings 
                               WHERE setting_key LIKE 'company_%' 
                               ORDER BY setting_key", []);
    
    if (empty($settings)) {
        echo "<p style='color: orange;'>⚠️ No company settings found!</p>";
        echo "<p>Anda perlu menambahkan setting berikut di menu <strong>Settings → General Settings</strong>:</p>";
        echo "<ul>";
        echo "<li><strong>company_name</strong> - Nama perusahaan/hotel</li>";
        echo "<li><strong>company_logo</strong> - Logo perusahaan (upload file)</li>";
        echo "<li><strong>company_address</strong> - Alamat lengkap</li>";
        echo "<li><strong>company_phone</strong> - Nomor telepon</li>";
        echo "<li><strong>company_email</strong> - Email</li>";
        echo "</ul>";
    } else {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f5f5f5;'><th>Setting Key</th><th>Value</th><th>Type</th><th>Status</th></tr>";
        
        $requiredSettings = ['company_name', 'company_logo', 'company_address', 'company_phone', 'company_email'];
        $foundSettings = [];
        
        foreach ($settings as $setting) {
            $foundSettings[] = $setting['setting_key'];
            $value = $setting['setting_value'];
            $isLogo = (strpos($setting['setting_key'], 'logo') !== false);
            
            // Check if logo file exists
            $fileStatus = '';
            if ($isLogo && $value) {
                $logoPath = dirname(__FILE__) . '/uploads/' . $value;
                if (file_exists($logoPath)) {
                    $fileStatus = '<span style="color: green;">✓ File exists</span>';
                    $fileSize = filesize($logoPath);
                    $fileStatus .= ' (' . number_format($fileSize / 1024, 2) . ' KB)';
                } else {
                    $fileStatus = '<span style="color: red;">✗ File not found</span>';
                    $fileStatus .= '<br><small>Looking for: ' . htmlspecialchars($logoPath) . '</small>';
                }
            }
            
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($setting['setting_key']) . "</strong></td>";
            echo "<td>" . ($isLogo && $value ? '<img src="' . BASE_URL . '/uploads/' . htmlspecialchars($value) . '" style="max-width: 100px; max-height: 50px;" onerror="this.style.display=\'none\'; this.nextSibling.style.display=\'inline\';"><span style="display:none; color: red;">Image not found</span><br>' : '') . htmlspecialchars($value) . "</td>";
            echo "<td>" . htmlspecialchars($setting['setting_type']) . "</td>";
            echo "<td>" . ($value ? '<span style="color: green;">✓ Set</span>' : '<span style="color: orange;">⚠ Empty</span>') . ($fileStatus ? '<br>' . $fileStatus : '') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check for missing required settings
        echo "<h3>Missing Required Settings:</h3>";
        $missingSettings = array_diff($requiredSettings, $foundSettings);
        
        if (empty($missingSettings)) {
            echo "<p style='color: green;'>✅ All required settings are configured!</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ The following settings are missing:</p>";
            echo "<ul>";
            foreach ($missingSettings as $missing) {
                echo "<li><strong>" . $missing . "</strong></li>";
            }
            echo "</ul>";
            echo "<p>Silahkan tambahkan di menu <strong>Settings → General Settings</strong></p>";
        }
    }
    
    echo "<hr>";
    echo "<h3>Invoice PDF Settings:</h3>";
    echo "<p>Ketika PDF invoice dicetak, sistem akan menggunakan:</p>";
    echo "<ul>";
    echo "<li><strong>Nama:</strong> company_name</li>";
    echo "<li><strong>Logo:</strong> company_logo atau invoice_logo (jika ada)</li>";
    echo "<li><strong>Alamat:</strong> company_address</li>";
    echo "<li><strong>Phone:</strong> company_phone</li>";
    echo "<li><strong>Email:</strong> company_email</li>";
    echo "</ul>";
    
    echo "<hr>";
    echo "<p style='color: green; font-weight: bold;'>✅ Check completed!</p>";
    echo "<p><a href='modules/sales/index.php'>← Back to Sales Invoices</a> | ";
    echo "<a href='modules/settings/index.php'>Go to Settings →</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
    background: #f5f5f5;
}
h2, h3 {
    color: #333;
}
table {
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
th {
    font-weight: bold;
}
a {
    color: #6366f1;
    text-decoration: none;
    font-weight: bold;
}
a:hover {
    text-decoration: underline;
}
</style>
