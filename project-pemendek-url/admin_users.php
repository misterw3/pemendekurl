<?php
require_once 'db.php';

// Check if user is admin
$user = getCurrentUser($conn);
if (!$user || $user['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Get all users
try {
    $stmt = $conn->query("
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
        GROUP BY u.id, u.username, u.email, u.role, u.created_at
        ORDER BY u.created_at DESC
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get statistics
    $totalUsers = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalAdmins = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
    $totalRegularUsers = $totalUsers - $totalAdmins;

} catch (PDOException $e) {
    echo "<div style='padding:20px; color:white;'>Error fetching data: " . $e->getMessage() . "</div>";
    $users = [];
    $totalUsers = 0;
    $totalAdmins = 0;
    $totalRegularUsers = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=JetBrains+Mono:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(8px);
            animation: fadeIn 0.3s;
        }
        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.3s;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .modal-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .modal-header h2 {
            color: white;
            font-size: 1.5rem;
            margin: 0;
        }
        .modal-body {
            margin-bottom: 1.5rem;
        }
        .modal-footer {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }
        .form-group {
            margin-bottom: 1.2rem;
        }
        .form-group label {
            display: block;
            color: #ccc;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        .form-group select {
            width: 100%;
            padding: 0.875rem;
            background: rgba(15, 15, 19, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s;
        }
        .form-group select:focus {
            border-color: var(--neon-violet);
            box-shadow: 0 0 0 2px rgba(189, 0, 255, 0.2);
        }
        .btn-cancel {
            padding: 0.75rem 1.5rem;
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        .btn-confirm {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, var(--neon-violet), var(--neon-pink));
            border: none;
            color: white;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(189, 0, 255, 0.3);
        }
        .action-btns {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .btn-edit, .btn-delete-user {
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            font-weight: 500;
        }
        .btn-edit {
            background: rgba(0, 247, 255, 0.2);
            color: var(--neon-cyan);
            border: 1px solid rgba(0, 247, 255, 0.3);
        }
        .btn-edit:hover {
            background: rgba(0, 247, 255, 0.3);
        }
        .btn-delete-user {
            background: rgba(255, 77, 77, 0.2);
            color: #ff4d4d;
            border: 1px solid rgba(255, 77, 77, 0.3);
        }
        .btn-delete-user:hover {
            background: rgba(255, 77, 77, 0.3);
        }
        .btn-add-user {
            padding: 0.875rem 1.5rem;
            background: linear-gradient(135deg, #4ade80, #22c55e);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 1.5rem;
        }
        .btn-add-user:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(74, 222, 128, 0.3);
        }
        .success-message {
            background: rgba(74, 222, 128, 0.2);
            border: 1px solid rgba(74, 222, 128, 0.3);
            color: #4ade80;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            display: none;
        }
        .success-message.show {
            display: block;
            animation: slideDown 0.3s;
        }
        @keyframes slideDown {
            from { transform: translateY(-10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="bg-mesh">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="grid-overlay"></div>
    </div>

    <nav class="navbar">
        <div style="margin-right: auto; font-weight: bold; font-size: 1.2rem;">
            👥 User Management
        </div>
        <a href="dashboard.php" class="nav-link">Dashboard</a>
        <a href="index.php" class="nav-link">Home</a>
        <a href="logout.php" class="nav-btn" style="background: rgba(255, 77, 77, 0.2); border-color: rgba(255, 77, 77, 0.2);">Logout</a>
    </nav>

    <div style="width: 100%; padding-top: 6rem; padding-bottom: 2rem;">
        
        <!-- Success Message -->
        <div id="successMessage" class="success-message" style="max-width: 90%; width: 1000px; margin: 0 auto 1rem auto;"></div>

        <!-- Stats Grid -->
        <div class="stats-grid" style="max-width: 1000px;">
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($totalUsers); ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($totalAdmins); ?></div>
                <div class="stat-label">Administrators</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($totalRegularUsers); ?></div>
                <div class="stat-label">Regular Users</div>
            </div>
        </div>

        <!-- User Table -->
        <div class="table-container glass-card" style="max-width: 1000px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                <h3 style="color: white; margin: 0;">All Users</h3>
                <button onclick="openAddUserModal()" class="btn-add-user">+ Add New User</button>
            </div>
            
            <?php if (count($users) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>URLs</th>
                        <th>Clicks</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?php echo $u['id']; ?></td>
                        <td>
                            <strong style="color: white;"><?php echo htmlspecialchars($u['username']); ?></strong>
                        </td>
                        <td style="color: #aaa;"><?php echo htmlspecialchars($u['email']); ?></td>
                        <td>
                            <span class="role-badge role-<?php echo $u['role']; ?>">
                                <?php echo ucfirst($u['role']); ?>
                            </span>
                        </td>
                        <td><?php echo number_format($u['total_urls']); ?></td>
                        <td><?php echo number_format($u['total_clicks']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                        <td>
                            <div class="action-btns">
                                <button onclick="openEditModal(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['username']); ?>', '<?php echo $u['role']; ?>')" class="btn-edit">
                                    Edit Role
                                </button>
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <button onclick="deleteUser(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['username']); ?>')" class="btn-delete-user">
                                    Delete
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p style="color: #aaa; text-align: center; padding: 2rem;">No users found.</p>
            <?php endif; ?>
        </div>

    </div>

    <!-- Edit Role Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit User Role</h2>
            </div>
            <div class="modal-body">
                <p style="color: #aaa; margin-bottom: 1rem;">
                    Editing role for: <strong id="editUsername" style="color: white;"></strong>
                </p>
                <div class="form-group">
                    <label>Role</label>
                    <select id="editRole">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="closeEditModal()" class="btn-cancel">Cancel</button>
                <button onclick="saveRole()" class="btn-confirm">Save Changes</button>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New User</h2>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" id="newUsername" class="input-field" placeholder="Enter username">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="newEmail" class="input-field" placeholder="Enter email">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" id="newPassword" class="input-field" placeholder="Enter password">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select id="newRole">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div id="addUserError" style="color: #ff4d4d; margin-top: 1rem; display: none;"></div>
            </div>
            <div class="modal-footer">
                <button onclick="closeAddUserModal()" class="btn-cancel">Cancel</button>
                <button onclick="addUser()" class="btn-confirm">Add User</button>
            </div>
        </div>
    </div>

    <script>
        let currentEditUserId = null;

        function openEditModal(userId, username, role) {
            currentEditUserId = userId;
            document.getElementById('editUsername').textContent = username;
            document.getElementById('editRole').value = role;
            document.getElementById('editModal').classList.add('show');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('show');
            currentEditUserId = null;
        }

        function openAddUserModal() {
            document.getElementById('addUserModal').classList.add('show');
            document.getElementById('newUsername').value = '';
            document.getElementById('newEmail').value = '';
            document.getElementById('newPassword').value = '';
            document.getElementById('newRole').value = 'user';
            document.getElementById('addUserError').style.display = 'none';
        }

        function closeAddUserModal() {
            document.getElementById('addUserModal').classList.remove('show');
        }

        async function saveRole() {
            const role = document.getElementById('editRole').value;
            
            try {
                const res = await fetch('admin_users_api.php?action=update_role', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: currentEditUserId, role: role })
                });
                
                const data = await res.json();
                
                if (data.success) {
                    showSuccess('User role updated successfully!');
                    closeEditModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert(data.error || 'Failed to update role');
                }
            } catch (err) {
                alert('Error: ' + err.message);
            }
        }

        async function deleteUser(userId, username) {
            if (!confirm(`Are you sure you want to delete user "${username}"?\n\nThis will also delete all their URLs and cannot be undone!`)) {
                return;
            }
            
            try {
                const res = await fetch('admin_users_api.php?action=delete_user', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: userId })
                });
                
                const data = await res.json();
                
                if (data.success) {
                    showSuccess('User deleted successfully!');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert(data.error || 'Failed to delete user');
                }
            } catch (err) {
                alert('Error: ' + err.message);
            }
        }

        async function addUser() {
            const username = document.getElementById('newUsername').value.trim();
            const email = document.getElementById('newEmail').value.trim();
            const password = document.getElementById('newPassword').value;
            const role = document.getElementById('newRole').value;
            const errorDiv = document.getElementById('addUserError');
            
            errorDiv.style.display = 'none';
            
            if (!username || !email || !password) {
                errorDiv.textContent = 'All fields are required';
                errorDiv.style.display = 'block';
                return;
            }
            
            try {
                const res = await fetch('admin_users_api.php?action=add_user', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, email, password, role })
                });
                
                const data = await res.json();
                
                if (data.success) {
                    showSuccess('User added successfully!');
                    closeAddUserModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    errorDiv.textContent = data.error || 'Failed to add user';
                    errorDiv.style.display = 'block';
                }
            } catch (err) {
                errorDiv.textContent = 'Error: ' + err.message;
                errorDiv.style.display = 'block';
            }
        }

        function showSuccess(message) {
            const successDiv = document.getElementById('successMessage');
            successDiv.textContent = '✓ ' + message;
            successDiv.classList.add('show');
            setTimeout(() => {
                successDiv.classList.remove('show');
            }, 3000);
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('show');
            }
        }
    </script>

</body>
</html>
