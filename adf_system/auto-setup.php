<?php
/**
 * ADF System - Hosting Auto-Setup Script
 * Create master database + tables + initial data
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Load config FIRST
define('APP_ACCESS', true);
$config_loaded = false;

try {
    require_once 'config/config.php';
    $config_loaded = true;
} catch (Exception $e) {
    echo "<div style='background:#f44336; color:white; padding:20px; font-size:16px;'>";
    echo "❌ Config load error: " . $e->getMessage();
    echo "</div>";
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>ADF System - Auto Setup</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        h1 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; }
        .step { background: #f9f9f9; padding: 20px; margin: 20px 0; border-left: 4px solid #2196F3; border-radius: 4px; }
        .success { background: #4CAF50 !important; color: white; border-left-color: #2E7D32 !important; }
        .error { background: #f44336 !important; color: white; border-left-color: #c62828 !important; }
        .warning { background: #ff9800 !important; color: white; border-left-color: #e65100 !important; }
        .info { background: #2196F3 !important; color: white; border-left-color: #0d47a1 !important; }
        pre { background: #272822; color: #f8f8f2; padding: 15px; border-radius: 4px; overflow-x: auto; }
        .progress-bar { width: 100%; height: 30px; background: #e0e0e0; border-radius: 4px; overflow: hidden; margin: 10px 0; }
        .progress-fill { height: 100%; background: #4CAF50; width: 0%; transition: width 0.3s; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; }
        ul, li { line-height: 1.8; }
    </style>
</head>
<body>
<div class="container">
    <h1>🚀 ADF System - Hosting Auto-Setup</h1>
    <p>This script will automatically setup your master database & tables.</p>
    
    <div class="progress-bar">
        <div class="progress-fill" id="progress" style="width: 0%;">0%</div>
    </div>

    <div id="log"></div>

</div>

<script>
    function log(message, type = 'info') {
        const logDiv = document.getElementById('log');
        const stepDiv = document.createElement('div');
        stepDiv.className = 'step ' + type;
        stepDiv.innerHTML = message;
        logDiv.appendChild(stepDiv);
        window.scrollTo(0, document.body.scrollHeight);
    }

    function updateProgress(percent) {
        document.getElementById('progress').style.width = percent + '%';
        document.getElementById('progress').textContent = percent + '%';
    }

    async function runSetup() {
        try {
            updateProgress(5);
            log('⏳ <strong>STEP 1:</strong> Checking database connection...', 'info');

            // Step 1: Check connection
            const checkResponse = await fetch('auto-setup-api.php?action=check_connection');
            const checkResult = await checkResponse.json();

            if (!checkResult.success) {
                log('❌ <strong>Connection Error:</strong> ' + checkResult.message, 'error');
                return;
            }
            log('✅ Database connection OK', 'success');

            updateProgress(15);
            log('⏳ <strong>STEP 2:</strong> Creating master database...', 'info');

            // Step 2: Create database
            const dbResponse = await fetch('auto-setup-api.php?action=create_database');
            const dbResult = await dbResponse.json();

            if (!dbResult.success) {
                log('❌ <strong>Database Creation Error:</strong> ' + dbResult.message, 'error');
                return;
            }
            log('✅ ' + dbResult.message, 'success');

            updateProgress(30);
            log('⏳ <strong>STEP 3:</strong> Creating tables...', 'info');

            // Step 3: Create tables
            const tablesResponse = await fetch('auto-setup-api.php?action=create_tables');
            const tablesResult = await tablesResponse.json();

            if (!tablesResult.success) {
                log('❌ <strong>Table Creation Error:</strong> ' + tablesResult.message, 'error');
                return;
            }
            log('✅ Tables created:<br>' + tablesResult.tables.join('<br>'), 'success');

            updateProgress(60);
            log('⏳ <strong>STEP 4:</strong> Inserting initial data...', 'info');

            // Step 4: Insert data
            const dataResponse = await fetch('auto-setup-api.php?action=insert_data');
            const dataResult = await dataResponse.json();

            if (!dataResult.success) {
                log('❌ <strong>Data Insertion Error:</strong> ' + dataResult.message, 'error');
                return;
            }
            log('✅ Initial data inserted:<ul><li>Roles: ' + dataResult.roles + '</li><li>Admin User: ' + dataResult.admin + '</li><li>Businesses: ' + dataResult.businesses + '</li></ul>', 'success');

            updateProgress(80);
            log('⏳ <strong>STEP 5:</strong> Verifying setup...', 'info');

            // Step 5: Verify
            const verifyResponse = await fetch('auto-setup-api.php?action=verify_setup');
            const verifyResult = await verifyResponse.json();

            if (!verifyResult.success) {
                log('❌ <strong>Verification Error:</strong> ' + verifyResult.message, 'error');
                return;
            }

            log('✅ Verification passed:<ul><li>Database: ' + verifyResult.database + '</li><li>Tables: ' + verifyResult.tables_count + ' tables</li><li>Users: ' + verifyResult.users_count + ' user(s)</li><li>Roles: ' + verifyResult.roles_count + ' role(s)</li></ul>', 'success');

            updateProgress(100);
            log('🎉 <strong>Setup Complete!</strong><br><br>You can now login:<ul><li><strong>URL:</strong> <a href="login.php" target="_blank">login.php</a></li><li><strong>Username:</strong> admin</li><li><strong>Password:</strong> admin123</li></ul><p style="color:red;"><strong>⚠️ NOTE:</strong> Delete <code>auto-setup.php</code> and <code>auto-setup-api.php</code> after setup for security!</p>', 'success');

        } catch (error) {
            log('❌ <strong>Error:</strong> ' + error.message, 'error');
        }
    }

    // Auto-start setup
    runSetup();
</script>

</body>
</html>
