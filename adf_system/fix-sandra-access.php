<?php
/**
 * Sandra Access Fixer
 * Check user access dan assign business + permissions
 */

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    // Connect to master database
    $pdo = new PDO(
        'mysql:host=localhost;dbname=adf_system;charset=utf8mb4',
        'root',
        ''
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $result = [
        'status' => 'success',
        'steps' => []
    ];

    // ============================================
    // STEP 1: Check if Sandra exists
    // ============================================
    $stmt = $pdo->prepare('SELECT id, username, email, full_name, role_id FROM users WHERE username = ?');
    $stmt->execute(['sandra']);
    $sandra = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sandra) {
        throw new Exception('❌ User sandra tidak ditemukan. Buat user dulu di User Setup!');
    }

    $sandraId = $sandra['id'];
    $result['steps'][] = [
        'step' => 'Check User Exists',
        'status' => '✅ PASS',
        'details' => [
            'user_id' => $sandraId,
            'username' => $sandra['username'],
            'email' => $sandra['email'],
            'full_name' => $sandra['full_name']
        ]
    ];

    // ============================================
    // STEP 2: Get all active businesses
    // ============================================
    $stmt = $pdo->query('SELECT id, business_name FROM businesses WHERE is_active = 1 ORDER BY business_name');
    $businesses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($businesses)) {
        throw new Exception('❌ Tidak ada business yang aktif!');
    }

    $result['steps'][] = [
        'step' => 'Get Available Businesses',
        'status' => '✅ PASS',
        'details' => [
            'count' => count($businesses),
            'businesses' => array_map(fn($b) => $b['business_name'], $businesses)
        ]
    ];

    // ============================================
    // STEP 3: Assign Sandra to all businesses
    // ============================================
    $assignedCount = 0;
    foreach ($businesses as $business) {
        $stmt = $pdo->prepare('
            INSERT IGNORE INTO user_business_assignment 
            (user_id, business_id, assigned_at) 
            VALUES (?, ?, NOW())
        ');
        $stmt->execute([$sandraId, $business['id']]);
        
        if ($stmt->rowCount() > 0) {
            $assignedCount++;
        }
    }

    $result['steps'][] = [
        'step' => 'Assign Sandra to Businesses',
        'status' => '✅ PASS',
        'details' => [
            'newly_assigned' => $assignedCount,
            'total_businesses' => count($businesses),
            'message' => 'Sandra sekarang bisa akses semua business'
        ]
    ];

    // ============================================
    // STEP 4: Get all menus
    // ============================================
    $stmt = $pdo->query('SELECT DISTINCT menu_code FROM user_menu_permissions GROUP BY menu_code ORDER BY menu_code');
    $menus = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($menus)) {
        throw new Exception('❌ Tidak ada menu yang tersedia!');
    }

    $result['steps'][] = [
        'step' => 'Get Available Menus',
        'status' => '✅ PASS',
        'details' => [
            'count' => count($menus),
            'menus' => $menus
        ]
    ];

    // ============================================
    // STEP 5: Grant Sandra "create" permission for all businesses & menus
    // ============================================
    $permissionCount = 0;
    foreach ($businesses as $business) {
        foreach ($menus as $menuCode) {
            $stmt = $pdo->prepare('
                INSERT INTO user_menu_permissions 
                (user_id, business_id, menu_code, can_view, can_create, can_edit, can_delete, created_at)
                VALUES (?, ?, ?, 1, 1, 0, 0, NOW())
                ON DUPLICATE KEY UPDATE 
                can_view = 1,
                can_create = 1
            ');
            $stmt->execute([$sandraId, $business['id'], $menuCode]);
            
            if ($stmt->rowCount() > 0) {
                $permissionCount++;
            }
        }
    }

    $result['steps'][] = [
        'step' => 'Grant Permissions (can_view, can_create)',
        'status' => '✅ PASS',
        'details' => [
            'permissions_set' => $permissionCount,
            'permission_level' => 'VIEW + CREATE',
            'applied_to' => count($businesses) . ' businesses × ' . count($menus) . ' menus',
            'message' => 'Sandra bisa VIEW dan CREATE di semua menu'
        ]
    ];

    // ============================================
    // STEP 6: Verify Sandra's access
    // ============================================
    $stmt = $pdo->prepare('
        SELECT COUNT(*) as count FROM user_business_assignment WHERE user_id = ?
    ');
    $stmt->execute([$sandraId]);
    $businessAssignCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $pdo->prepare('
        SELECT COUNT(*) as count FROM user_menu_permissions WHERE user_id = ? AND can_view = 1
    ');
    $stmt->execute([$sandraId]);
    $permissionVerifyCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $result['steps'][] = [
        'step' => 'Verify Sandra\'s Access',
        'status' => '✅ PASS',
        'details' => [
            'businesses_assigned' => intval($businessAssignCount),
            'menus_with_view_permission' => intval($permissionVerifyCount),
            'status' => 'READY TO LOGIN'
        ]
    ];

    // ============================================
    // FINAL SUMMARY
    // ============================================
    $result['summary'] = [
        'user' => 'sandra',
        'user_id' => $sandraId,
        'full_name' => $sandra['full_name'],
        'businesses_access' => intval($businessAssignCount),
        'total_menus' => count($menus),
        'can_login_now' => true,
        'message' => '✅ SANDRA BISA LOGIN SEKARANG!'
    ];

    $result['instructions'] = [
        'step1' => 'Go to: http://localhost:8081/adf_system/login.php',
        'step2' => 'Login dengan:',
        'step3' => 'Username: sandra',
        'step4' => 'Password: [password yang Anda atur saat create user]',
        'step5' => 'Sandra akan bisa pilih business saat pertama kali login'
    ];

} catch (Exception $e) {
    http_response_code(500);
    $result = [
        'status' => 'error',
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ];
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
