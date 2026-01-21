<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Minimalist URL Shortener</title>
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

    <nav class="navbar">
        <a href="index.php" class="nav-link">Home</a>
    </nav>

    <div class="container">
        <h1>Create Account</h1>
        <p class="subtitle">Join us to manage your links efficiently.</p>

        <div class="glass-card">
            <form id="registerForm" class="auth-form" onsubmit="return false;">
                <div>
                    <label class="form-label">Username</label>
                    <input type="text" id="username" class="input-field" required>
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" id="email" class="input-field" required>
                </div>
                <div>
                    <label class="form-label">Password</label>
                    <input type="password" id="password" class="input-field" required>
                </div>
                
                <button type="submit" id="submitBtn" class="btn-primary" style="margin-top: 1rem;">
                    Create Account
                </button>
            </form>
            <div id="msg" class="error-message hidden" style="margin-top: 1rem;"></div>
            
            <p style="margin-top: 1.5rem; font-size: 0.9rem; color: #aaa;">
                Already have an account? <a href="login.php" style="color: var(--neon-cyan)">Login</a>
            </p>
        </div>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            const btn = document.getElementById('submitBtn');
            const msg = document.getElementById('msg');
            btn.disabled = true;
            btn.textContent = 'Processsing...';
            msg.classList.add('hidden');

            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            try {
                const res = await fetch('auth_action.php?action=register', {
                    method: 'POST',
                    body: JSON.stringify({ username, email, password })
                });
                const data = await res.json();
                
                if (data.success) {
                    msg.style.color = '#4ade80';
                    msg.textContent = data.message;
                    msg.classList.remove('hidden');
                    setTimeout(() => window.location.href = 'login.php', 2000);
                } else {
                    throw new Error(data.error || 'Registration failed');
                }
            } catch (err) {
                msg.style.color = '#ff4d4d';
                msg.textContent = err.message;
                msg.classList.remove('hidden');
                btn.disabled = false;
                btn.textContent = 'Create Account';
            }
        });
    </script>
</body>
</html>
