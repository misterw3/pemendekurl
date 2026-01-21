<?php
/**
 * Database Configuration and Connection
 * URL Shortener Application
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pemendek_url');
define('DB_CHARSET', 'utf8mb4');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Global connection variable
$conn = null;

try {
    // Check if PDO MySQL driver is available
    if (!extension_loaded('pdo_mysql')) {
        throw new Exception(
            '<h2>PDO MySQL Driver Not Found</h2>' .
            '<p>Please enable the PDO MySQL extension in your php.ini file:</p>' .
            '<ol>' .
            '<li>Open <code>php.ini</code> file (usually in <code>C:\\xampp\\php\\php.ini</code>)</li>' .
            '<li>Find the line: <code>;extension=pdo_mysql</code></li>' .
            '<li>Remove the semicolon (;) to uncomment it: <code>extension=pdo_mysql</code></li>' .
            '<li>Restart Apache server</li>' .
            '</ol>' .
            '<p><strong>Or run this command in terminal:</strong></p>' .
            '<pre>php -m | findstr pdo_mysql</pre>' .
            '<p>If it returns nothing, PDO MySQL is not enabled.</p>'
        );
    }

    // Create PDO connection
    $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
    ];
    
    $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    // Create database if not exists
    $conn->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_CHARSET . "_unicode_ci");
    $conn->exec("USE `" . DB_NAME . "`");
    
    // Create tables if not exist
    createTables($conn);
    
} catch (PDOException $e) {
    // Database connection error
    $error_message = '<div style="font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px;">';
    $error_message .= '<h2 style="color: #856404; margin-top: 0;">⚠️ Database Connection Error</h2>';
    $error_message .= '<p style="color: #856404;"><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    $error_message .= '<h3 style="color: #856404;">Troubleshooting Steps:</h3>';
    $error_message .= '<ol style="color: #856404;">';
    $error_message .= '<li>Make sure XAMPP MySQL is running</li>';
    $error_message .= '<li>Check database credentials in <code>db.php</code></li>';
    $error_message .= '<li>Verify MySQL service is started in XAMPP Control Panel</li>';
    $error_message .= '</ol>';
    $error_message .= '</div>';
    die($error_message);
} catch (Exception $e) {
    // Other errors (like missing PDO driver)
    die('<div style="font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; color: #721c24;">' . $e->getMessage() . '</div>');
}

/**
 * Create necessary database tables
 */
function createTables($conn) {
    // Users table
    $conn->exec("CREATE TABLE IF NOT EXISTS `users` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `username` VARCHAR(50) NOT NULL UNIQUE,
        `email` VARCHAR(100) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `role` ENUM('user', 'admin') DEFAULT 'user',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_username` (`username`),
        INDEX `idx_email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // URLs table
    $conn->exec("CREATE TABLE IF NOT EXISTS `urls` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `user_id` INT(11) NULL,
        `alias` VARCHAR(10) NOT NULL UNIQUE,
        `original_url` TEXT NOT NULL,
        `clicks` INT(11) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `last_clicked_at` TIMESTAMP NULL DEFAULT NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_alias` (`alias`),
        INDEX `idx_user_id` (`user_id`),
        INDEX `idx_alias` (`alias`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Click analytics table
    $conn->exec("CREATE TABLE IF NOT EXISTS `click_analytics` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `url_id` INT(11) NOT NULL,
        `ip_address` VARCHAR(45) NULL,
        `user_agent` TEXT NULL,
        `referer` TEXT NULL,
        `clicked_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_url_id` (`url_id`),
        INDEX `idx_clicked_at` (`clicked_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Create default admin user if not exists
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        if ($stmt->fetchColumn() == 0) {
            // Password: admin123
            $hashedPassword = password_hash('admin123', PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute(['admin', 'admin@urlshortener.com', $hashedPassword, 'admin']);
        }
    } catch (PDOException $e) {
        // Ignore if admin already exists
    }
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current logged in user
 */
function getCurrentUser($conn) {
    if (!isLoggedIn()) {
        return null;
    }
    
    try {
        $stmt = $conn->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Redirect to login if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Redirect to login if not admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Sanitize output
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Get base URL
 */
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    if ($dir == '/') $dir = '';
    return $protocol . $host . $dir;
}
?>
