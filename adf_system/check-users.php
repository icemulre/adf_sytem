<?php
define('APP_ACCESS', true);

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=adf_system;charset=utf8mb4",
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Get all users with roles
    $stmt = $pdo->query("
        SELECT u.id, u.username, u.full_name, r.role_code, r.role_name
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        ORDER BY u.username
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>All Users & Roles</h2>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Role</th><th>Role Code</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
        echo "<td>" . htmlspecialchars($user['full_name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['role_name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['role_code']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>Roles Available</h2>";
    $roles = $pdo->query("SELECT * FROM roles ORDER BY role_code")->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Role Code</th><th>Role Name</th></tr>";
    foreach ($roles as $role) {
        echo "<tr>";
        echo "<td>" . $role['id'] . "</td>";
        echo "<td>" . $role['role_code'] . "</td>";
        echo "<td>" . $role['role_name'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
