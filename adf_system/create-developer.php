<?php
define('APP_ACCESS', true);

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=adf_system;charset=utf8mb4",
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Get developer role ID
    $stmt = $pdo->query("SELECT id FROM roles WHERE role_code = 'developer'");
    $devRole = $stmt->fetch(PDO::FETCH_ASSOC);
    $devRoleId = $devRole['id'] ?? 4;
    
    // Create developer user
    $password = 'developer123';
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password, full_name, phone, role_id, is_active)
        VALUES ('developer', 'developer@adfsystem.local', ?, 'Developer User', '0000000000', ?, 1)
        ON DUPLICATE KEY UPDATE password = ?, is_active = 1
    ");
    $stmt->execute([$hashedPassword, $devRoleId, $hashedPassword]);
    
    echo "<h2>✅ Developer User Created/Updated</h2>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Field</th><th>Value</th></tr>";
    echo "<tr><td>Username</td><td><strong>developer</strong></td></tr>";
    echo "<tr><td>Password</td><td><strong>developer123</strong></td></tr>";
    echo "<tr><td>Email</td><td>developer@adfsystem.local</td></tr>";
    echo "<tr><td>Role</td><td>Developer</td></tr>";
    echo "</table>";
    
    // Verify
    echo "<h2>Verification</h2>";
    $verify = $pdo->query("
        SELECT u.username, u.email, r.role_code, u.created_at
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        WHERE u.username = 'developer'
    ")->fetch(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    print_r($verify);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
