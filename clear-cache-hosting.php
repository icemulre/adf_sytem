<?php
/**
 * Clear OPCache on hosting
 * Upload to hosting and access: https://adfsystem.online/adf_system/clear-cache-hosting.php
 */

// Try to clear OPCache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPCache cleared successfully!<br>";
} else {
    echo "⚠️ OPCache not active or can't be cleared<br>";
}

// Show PHP info
echo "<br><strong>PHP Version:</strong> " . phpversion() . "<br>";
echo "<strong>OPCache Status:</strong> " . (extension_loaded('Zend OPcache') ? 'Enabled' : 'Disabled') . "<br>";
echo "<br><a href='developer/login.php'>Go to Login Page</a>";
?>
