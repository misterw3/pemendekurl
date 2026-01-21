<?php
require_once 'db.php';
$user = getCurrentUser($conn);

if (!$user) {
    header("Location: login.php");
    exit;
}

// Handle Delete Action
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($user['role'] === 'admin') {
        $stmt = $conn->prepare("DELETE FROM urls WHERE id = ?");
        $stmt->execute([$id]);
    } else {
        $stmt = $conn->prepare("DELETE FROM urls WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user['id']]);
    }
    header("Location: dashboard.php");
    exit;
}

// Fetch Stats & URLs
if ($user['role'] === 'admin') {
    $total_links = $conn->query("SELECT COUNT(*) FROM urls")->fetchColumn();
    $total_clicks = $conn->query("SELECT SUM(clicks) FROM urls")->fetchColumn();
    
    // Admin sees all URLs (limited to last 50 for demo)
    $stmt = $conn->query("SELECT u.*, us.username FROM urls u LEFT JOIN users us ON u.user_id = us.id ORDER BY u.created_at DESC LIMIT 50");
    $urls = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM urls WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $total_links = $stmt->fetchColumn();
    
    $stmt = $conn->prepare("SELECT SUM(clicks) FROM urls WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $total_clicks = $stmt->fetchColumn() ?: 0;
    
    $stmt = $conn->prepare("SELECT * FROM urls WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user['id']]);
    $urls = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Minimalist URL Shortener</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=JetBrains+Mono:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script> 
    <!-- Note: FontAwesome is optional, used text for icons to keep it light if needed, but nice to have -->
</head>
<body>
    <div class="bg-mesh">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="grid-overlay"></div>
    </div>

    <nav class="navbar">
        <div style="margin-right: auto; font-weight: bold; font-size: 1.2rem;">
            Dashboard <span class="role-badge role-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span>
        </div>
        <span style="color: #aaa;">Hello, <?php echo htmlspecialchars($user['username']); ?></span>
        <?php if($user['role'] === 'admin'): ?>
        <a href="admin_users.php" class="nav-link" style="color: var(--neon-cyan);">👥 Users</a>
        <?php endif; ?>
        <a href="index.php" class="nav-link">Home</a>
        <a href="logout.php" class="nav-btn" style="background: rgba(255, 77, 77, 0.2); border-color: rgba(255, 77, 77, 0.2);">Logout</a>
    </nav>

    <div style="width: 100%; padding-top: 6rem; padding-bottom: 2rem;">
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($total_links); ?></div>
                <div class="stat-label">Total Links</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($total_clicks); ?></div>
                <div class="stat-label">Total Clicks</div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container glass-card">
            <h3 style="margin-bottom: 1.5rem; color: white;">Manage Links</h3>
            
            <?php if (count($urls) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Alias</th>
                        <th style="width: 40%;">Original URL</th>
                        <th>Created</th>
                        <th>Clicks</th>
                        <?php if($user['role'] === 'admin'): ?><th>User</th><?php endif; ?>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($urls as $url): 
                        // Determine short URL base.
                        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
                        $host = $_SERVER['HTTP_HOST'];
                        $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
                        if ($dir == '/') $dir = '';
                        $base = $protocol . $host . $dir;
                    ?>
                    <tr>
                        <td>
                            <a href="<?php echo $base . '/' . $url['alias']; ?>" target="_blank" style="color: var(--neon-cyan); text-decoration: none; font-family: 'JetBrains Mono', monospace;">
                                <?php echo $url['alias']; ?>
                            </a>
                        </td>
                        <td>
                            <div style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #aaa;">
                                <?php echo htmlspecialchars($url['original_url']); ?>
                            </div>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($url['created_at'])); ?></td>
                        <td><?php echo $url['clicks']; ?></td>
                        <?php if($user['role'] === 'admin'): ?>
                            <td><?php echo htmlspecialchars($url['username'] ?? 'Anonymous'); ?></td>
                        <?php endif; ?>
                        <td>
                            <a href="?delete=<?php echo $url['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p style="color: #aaa; text-align: center; padding: 2rem;">No links found. <a href="index.php" style="color: var(--neon-cyan)">Create one now</a>.</p>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>
