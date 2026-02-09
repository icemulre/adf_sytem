<?php
/**
 * GitHub Webhook Deploy Script
 * Trigger automatic git pull when commits pushed to GitHub
 * Uses .env file for secure credential management
 */

// Load .env configuration
$__DIR__ = __DIR__;
if (file_exists($__DIR__ . '/.env')) {
    $env_lines = file($__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env_lines as $line) {
        if (strpos($line, '#') === 0) continue;
        list($key, $value) = array_map('trim', explode('=', $line, 2));
        if (!empty($key) && !empty($value)) {
            putenv("$key=$value");
            define(strtoupper($key), $value);
        }
    }
}

// Security token from .env
define('WEBHOOK_TOKEN', getenv('WEBHOOK_TOKEN') ?: 'adf_deploy_2026_secret');

// Log file
$logFile = __DIR__ . '/deploy.log';

function writeLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Verify webhook token
$token = $_GET['token'] ?? $_POST['token'] ?? null;

if ($token !== WEBHOOK_TOKEN) {
    http_response_code(401);
    echo "❌ Unauthorized";
    writeLog("FAILED: Invalid or missing token");
    exit;
}

// Get payload
$payload = isset($_POST['payload']) ? json_decode($_POST['payload'], true) : json_decode(file_get_contents('php://input'), true);

echo "<pre>";
echo "🚀 <strong>ADF System Deployment Started</strong>\n";
echo "================================================\n\n";

// STEP 1: Pull latest from GitHub
echo "📥 STEP 1: Pulling latest code from GitHub...\n";
$repo_path = __DIR__;
$output = shell_exec("cd $repo_path && git pull origin main 2>&1");
writeLog("Git Pull Output: $output");
echo $output . "\n";

// STEP 2: Verify connection
echo "\n📡 STEP 2: Verifying database connection...\n";
try {
    require_once 'config/config.php';
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Database connected: " . DB_NAME . "\n";
    writeLog("Database check: OK");
} catch (Exception $e) {
    echo "⚠️ Database warning: " . $e->getMessage() . "\n";
    writeLog("Database check failed: " . $e->getMessage());
}

// STEP 3: Clear cache
echo "\n🧹 STEP 3: Clearing cache...\n";
$cache_dirs = [
    __DIR__ . '/cache',
    __DIR__ . '/tmp',
];

foreach ($cache_dirs as $dir) {
    if (is_dir($dir)) {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? rmdir($path) : unlink($path);
        }
        echo "✅ Cleared: $dir\n";
        writeLog("Cache cleared: $dir");
    }
}

// STEP 4: Summary
echo "\n================================================\n";
echo "✅ <strong>Deployment Completed Successfully!</strong>\n";
echo "================================================\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
writeLog("Deployment completed successfully");

echo "\n<a href='index.php'>← Back to Application</a>";
echo "</pre>";
?>
