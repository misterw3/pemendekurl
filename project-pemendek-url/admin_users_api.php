<?php
/**
 * Admin User Management API
 * Handles user CRUD operations for administrators
 */

header('Content-Type: application/json');
require_once 'db.php';

// Check if user is admin
$user = getCurrentUser($conn);
if (!$user || $user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized. Admin access required.']);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$action = $_GET['action'] ?? '';
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true) ?: []; // Fallback to empty array if null

try {
    switch ($action) {
        
        // Update user role
        case 'update_role':
            $userId = $input['user_id'] ?? null;
            $newRole = $input['role'] ?? null;
            
            if (!$userId || !$newRole) {
                throw new Exception('User ID and role are required');
            }
            
            if (!in_array($newRole, ['user', 'admin'])) {
                throw new Exception('Invalid role. Must be "user" or "admin"');
            }
            
            // Prevent admin from demoting themselves
            if ($userId == $_SESSION['user_id'] && $newRole !== 'admin') {
                throw new Exception('You cannot change your own role');
            }
            
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$newRole, $userId]);
            
            // Update session if changing own role (shouldn't happen but just in case)
            if ($userId == $_SESSION['user_id']) {
                $_SESSION['role'] = $newRole;
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'User role updated successfully'
            ]);
            break;
        
        // Delete user
        case 'delete_user':
            $userId = $input['user_id'] ?? null;
            
            if (!$userId) {
                throw new Exception('User ID is required');
            }
            
            // Prevent admin from deleting themselves
            if ($userId == $_SESSION['user_id']) {
                throw new Exception('You cannot delete your own account');
            }
            
            // Check if user exists
            $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $userToDelete = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$userToDelete) {
                throw new Exception('User not found');
            }
            
            // Delete user's URLs first (cascade)
            $stmt = $conn->prepare("DELETE FROM urls WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            // Delete user
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'User and all associated URLs deleted successfully'
            ]);
            break;
        
        // Add new user
        case 'add_user':
            $username = trim($input['username'] ?? '');
            $email = trim($input['email'] ?? '');
            $password = $input['password'] ?? '';
            $role = $input['role'] ?? 'user';
            
            // Validation
            if (empty($username) || empty($email) || empty($password)) {
                throw new Exception('All fields are required');
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email format');
            }
            
            if (strlen($password) < 6) {
                throw new Exception('Password must be at least 6 characters');
            }
            
            if (!in_array($role, ['user', 'admin'])) {
                throw new Exception('Invalid role');
            }
            
            // Check if username or email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                throw new Exception('Username or email already exists');
            }
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            
            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashedPassword, $role]);
            
            $newUserId = $conn->lastInsertId();
            
            echo json_encode([
                'success' => true,
                'message' => 'User created successfully',
                'user_id' => $newUserId
            ]);
            break;
        
        // Reset user password
        case 'reset_password':
            $userId = $input['user_id'] ?? null;
            $newPassword = $input['new_password'] ?? '';
            
            if (!$userId || empty($newPassword)) {
                throw new Exception('User ID and new password are required');
            }
            
            if (strlen($newPassword) < 6) {
                throw new Exception('Password must be at least 6 characters');
            }
            
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Password reset successfully'
            ]);
            break;
        
        // Get user details
        case 'get_user':
            $userId = $input['user_id'] ?? null;
            
            if (!$userId) {
                throw new Exception('User ID is required');
            }
            
            $stmt = $conn->prepare("
                SELECT 
                    u.id,
                    u.username,
                    u.email,
                    u.role,
                    u.created_at,
                    COUNT(url.id) as total_urls,
                    COALESCE(SUM(url.clicks), 0) as total_clicks
                FROM users u
                LEFT JOIN urls url ON u.id = url.user_id
                WHERE u.id = ?
                GROUP BY u.id
            ");
            $stmt->execute([$userId]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$userData) {
                throw new Exception('User not found');
            }
            
            echo json_encode([
                'success' => true,
                'user' => $userData
            ]);
            break;
        
        // Get all users (with pagination)
        case 'get_all_users':
            $page = $input['page'] ?? 1;
            $limit = $input['limit'] ?? 50;
            $offset = ($page - 1) * $limit;
            
            $stmt = $conn->prepare("
                SELECT 
                    u.id,
                    u.username,
                    u.email,
                    u.role,
                    u.created_at,
                    COUNT(url.id) as total_urls,
                    COALESCE(SUM(url.clicks), 0) as total_clicks
                FROM users u
                LEFT JOIN urls url ON u.id = url.user_id
                GROUP BY u.id
                ORDER BY u.created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$limit, $offset]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count
            $totalUsers = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
            
            echo json_encode([
                'success' => true,
                'users' => $users,
                'total' => $totalUsers,
                'page' => $page,
                'limit' => $limit
            ]);
            break;
        
        default:
            throw new Exception('Invalid action');
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>
