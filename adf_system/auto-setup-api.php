<?php
/**
 * ADF System - Auto Setup API Backend
 */

define('APP_ACCESS', true);
require_once 'config/config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'check_connection':
            checkConnection();
            break;
        
        case 'create_database':
            createDatabase();
            break;
        
        case 'create_tables':
            createTables();
            break;
        
        case 'insert_data':
            insertData();
            break;
        
        case 'verify_setup':
            verifySetup();
            break;
        
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function checkConnection() {
    try {
        // Try to connect without database first
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Connected to MySQL server'
        ]);
    } catch (Exception $e) {
        throw new Exception('MySQL connection failed: ' . $e->getMessage());
    }
}

function createDatabase() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Create database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        echo json_encode([
            'success' => true,
            'message' => 'Database ' . DB_NAME . ' created/verified'
        ]);
    } catch (Exception $e) {
        throw new Exception('Database creation failed: ' . $e->getMessage());
    }
}

function createTables() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        $tables = [];
        
        // Disable foreign key checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
        
        // 1. ROLES TABLE
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS roles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                role_name VARCHAR(50) UNIQUE NOT NULL,
                role_code VARCHAR(20) UNIQUE NOT NULL,
                description TEXT,
                is_system_role TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_role_code (role_code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $tables[] = 'roles';
        
        // 2. USERS TABLE
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                full_name VARCHAR(100) NOT NULL,
                phone VARCHAR(20),
                role_id INT NOT NULL,
                is_active TINYINT(1) DEFAULT 1,
                last_login DATETIME,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                created_by INT,
                
                FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_username (username),
                INDEX idx_email (email),
                INDEX idx_role (role_id),
                INDEX idx_is_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $tables[] = 'users';
        
        // 3. USER PREFERENCES TABLE
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS user_preferences (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                theme VARCHAR(50) DEFAULT 'dark',
                language VARCHAR(10) DEFAULT 'id',
                notifications_enabled TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE KEY unique_user_pref (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $tables[] = 'user_preferences';
        
        // 4. BUSINESSES TABLE
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS businesses (
                id VARCHAR(50) PRIMARY KEY,
                business_code VARCHAR(50) UNIQUE NOT NULL,
                business_name VARCHAR(100) NOT NULL,
                business_type VARCHAR(50),
                address TEXT,
                phone VARCHAR(20),
                email VARCHAR(100),
                website VARCHAR(255),
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                
                INDEX idx_code (business_code),
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $tables[] = 'businesses';
        
        // 5. USER MENU PERMISSIONS TABLE
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS user_menu_permissions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                business_id VARCHAR(50) NOT NULL,
                menu_code VARCHAR(100) NOT NULL,
                can_view TINYINT(1) DEFAULT 1,
                can_create TINYINT(1) DEFAULT 0,
                can_edit TINYINT(1) DEFAULT 0,
                can_delete TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
                UNIQUE KEY unique_permission (user_id, business_id, menu_code),
                INDEX idx_user (user_id),
                INDEX idx_business (business_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $tables[] = 'user_menu_permissions';
        
        // Re-enable foreign key checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
        
        echo json_encode([
            'success' => true,
            'tables' => $tables
        ]);
    } catch (Exception $e) {
        throw new Exception('Table creation failed: ' . $e->getMessage());
    }
}

function insertData() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
        
        // Clear existing data
        $pdo->exec("DELETE FROM roles");
        $pdo->exec("DELETE FROM users");
        $pdo->exec("DELETE FROM user_preferences");
        $pdo->exec("DELETE FROM businesses");
        
        // Insert roles
        $roles = [
            ['admin', 'Admin', 'System administrator'],
            ['manager', 'Manager', 'Business manager'],
            ['staff', 'Staff', 'Regular staff'],
            ['developer', 'Developer', 'System developer']
        ];
        
        foreach ($roles as $role) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO roles (role_code, role_name, description) VALUES (?, ?, ?)");
            $stmt->execute($role);
        }
        $roles_count = count($roles);
        
        // Get admin role id
        $stmt = $pdo->prepare("SELECT id FROM roles WHERE role_code = 'admin'");
        $stmt->execute();
        $adminRole = $stmt->fetch(PDO::FETCH_ASSOC);
        $adminRoleId = $adminRole['id'] ?? 1;
        
        // Insert admin user
        $adminPassword = password_hash('admin123', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, email, password, full_name, phone, role_id, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@adfsystem.local', $adminPassword, 'Administrator', '0000000000', $adminRoleId, 1]);
        $admin_inserted = $stmt->rowCount() > 0 ? 1 : 0;
        
        // Get admin user id
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
        $stmt->execute();
        $adminUser = $stmt->fetch(PDO::FETCH_ASSOC);
        $adminUserId = $adminUser['id'] ?? 1;
        
        // Insert user preferences
        $stmt = $pdo->prepare("INSERT IGNORE INTO user_preferences (user_id, theme, language) VALUES (?, ?, ?)");
        $stmt->execute([$adminUserId, 'dark', 'id']);
        
        // Insert businesses
        $businesses = [
            ['narayana-hotel', 'NARAYANAHOTEL', 'Narayana Hotel', 'hotel'],
            ['bens-cafe', 'BENSCAFE', 'Bens Cafe', 'cafe']
        ];
        
        foreach ($businesses as $biz) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO businesses (id, business_code, business_name, business_type) VALUES (?, ?, ?, ?)");
            $stmt->execute($biz);
        }
        $businesses_count = count($businesses);
        
        // Grant permissions to admin for all businesses
        $menus = ['dashboard', 'cashbook', 'divisions', 'frontdesk', 'procurement', 'sales', 'reports', 'settings', 'users'];
        
        foreach ($businesses as $biz) {
            $bizId = $biz[0];
            foreach ($menus as $menu) {
                $stmt = $pdo->prepare("INSERT IGNORE INTO user_menu_permissions (user_id, business_id, menu_code, can_view, can_create, can_edit, can_delete) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$adminUserId, $bizId, $menu, 1, 1, 1, 1]);
            }
        }
        
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
        
        echo json_encode([
            'success' => true,
            'roles' => $roles_count,
            'admin' => ($admin_inserted > 0 ? 'Created' : 'Already exists'),
            'businesses' => $businesses_count
        ]);
    } catch (Exception $e) {
        throw new Exception('Data insertion failed: ' . $e->getMessage());
    }
}

function verifySetup() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Count tables
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM information_schema.TABLES WHERE TABLE_SCHEMA = '" . DB_NAME . "'");
        $tables_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Count users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $users_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Count roles
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM roles");
        $roles_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Check admin user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin' LIMIT 1");
        $stmt->execute();
        $adminExists = $stmt->rowCount() > 0;
        
        if (!$adminExists || $users_count == 0) {
            throw new Exception('Admin user not found or setup incomplete');
        }
        
        echo json_encode([
            'success' => true,
            'database' => DB_NAME,
            'tables_count' => $tables_count,
            'users_count' => $users_count,
            'roles_count' => $roles_count
        ]);
    } catch (Exception $e) {
        throw new Exception('Verification failed: ' . $e->getMessage());
    }
}
?>
