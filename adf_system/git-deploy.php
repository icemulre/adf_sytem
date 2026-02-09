<?php
/**
 * Simple Git Deploy Script - No webhook token needed
 * Just for initial setup
 */

echo "<h1>🚀 ADF System Git Deployer</h1>";
echo "<hr>";

$repoPath = __DIR__;
$executing = true;

// STEP 1: Git Pull
echo "<h3>Step 1: Git Pull Latest Code</h3>";
echo "<pre>";
$output = shell_exec("cd $repoPath && git pull origin main 2>&1");
echo htmlspecialchars($output);
echo "</pre>";

// STEP 2: Check Files
echo "<h3>Step 2: Verifying Files</h3>";
$requiredFiles = [
    'config/config.php',
    'login.php',
    'index.php',
    'deploy-webhook.php',
    'login-debug.php'
];

foreach ($requiredFiles as $file) {
    $path = "$repoPath/$file";
    if (file_exists($path)) {
        echo "✅ $file<br>";
    } else {
        echo "❌ MISSING: $file<br>";
    }
}

echo "<br>";

// STEP 3: Check Database
echo "<h3>Step 3: Database Connection</h3>";
try {
    require_once 'config/config.php';
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS
    );
    echo "✅ Database connected: " . DB_NAME . "<br>";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

echo "<br>";

// Final message
echo "<h3>✅ Deployment Complete!</h3>";
echo "<p>You can now access:</p>";
echo "<ul>";
echo "<li><a href='login.php' target='_blank'>Login page</a></li>";
echo "<li><a href='login-debug.php' target='_blank'>Login Debug</a></li>";
echo "<li><a href='hosting-debug.php' target='_blank'>Hosting Debug</a></li>";
echo "</ul>";

echo "<hr>";
echo "<p style='color:red;'><strong>⚠️ WARNING:</strong> Delete this script after deployment for security!</p>";
echo "<p>Delete file: <code>git-deploy.php</code></p>";
?>
