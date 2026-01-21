<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Minimalist URL Shortener</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="bg-mesh">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
        <div class="grid-overlay"></div>
    </div>

    <!-- Nav -->
    <nav class="navbar">
        <a href="index.php" class="nav-link">Home</a>
    </nav>

    <div class="container">
        <h1>Welcome Back</h1>
        <p class="subtitle">Enter your credentials to access your dashboard.</p>

        <div class="glass-card">
            <form id="loginForm" class="auth-form" onsubmit="return false;">
                <div>
                    <label class="form-label">Username</label>
                    <input type="text" id="username" class="input-field" required>
                </div>
                <div>
                    <label class="form-label">Password</label>
                    <input type="password" id="password" class="input-field" required>
                </div>
                
                <button type="submit" id="submitBtn" class="btn-primary" style="margin-top: 1rem;">
                    Sign In
                </button>
            </form>
            <div id="errorMsg" class="error-message hidden" style="color: #ff4d4d; margin-top: 1rem;"></div>
            
            <p style="margin-top: 1.5rem; font-size: 0.9rem; color: #aaa;">
                Don't have an account? <a href="register.php" style="color: var(--neon-cyan)">Register</a>
            </p>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            const btn = document.getElementById('submitBtn');
            const errorMsg = document.getElementById('errorMsg');
            btn.disabled = true;
            btn.textContent = 'Verifying...';
            errorMsg.classList.add('hidden');

            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            try {
                const res = await fetch('auth_action.php?action=login', {
                    method: 'POST',
                    body: JSON.stringify({ username, password })
                });
                const data = await res.json();
                
                if (data.success) {
                    window.location.href = data.redirect || 'dashboard.php';
                } else {
                    throw new Error(data.error || 'Login failed');
                }
            } catch (err) {
                errorMsg.textContent = err.message;
                errorMsg.classList.remove('hidden');
                btn.disabled = false;
                btn.textContent = 'Sign In';
            }
        });
    </script>
</body>
</html>
