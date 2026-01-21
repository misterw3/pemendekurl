<?php require_once 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minimalist URL Shortener</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=JetBrains+Mono:wght@400&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <?php if(isLoggedIn()): ?>
            <a href="dashboard.php" class="nav-link">Dashboard</a>
            <a href="logout.php" class="nav-btn">Logout</a>
        <?php else: ?>
            <a href="login.php" class="nav-link">Login</a>
            <a href="register.php" class="nav-btn">Get Started</a>
        <?php endif; ?>
    </nav>

    <!-- Animated Background -->
    <div class="bg-mesh">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
        <div class="grid-overlay"></div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <h1 class="stagger-1">Shorten.</h1>
        <p class="subtitle stagger-2">Simplify your links with a premium touch.</p>

        <div class="glass-card stagger-3">
            <form id="shortenForm" onsubmit="return false;">
                <div class="input-group">
                    <input type="text" id="urlInput" class="input-field" placeholder="Paste your long URL here..." autocomplete="off">
                </div>
                <button type="submit" id="shortenBtn" class="btn-primary">
                    <span id="btnText">Shorten URL</span>
                    <span id="btnLoader" class="loading-spinner hidden"></span>
                </button>
            </form>

            <div id="errorMsg" class="error-message hidden" style="color: #ff4d4d; margin-top: 1rem; font-size: 0.9rem;"></div>
        </div>

        <!-- Result Card -->
        <div id="resultCard" class="result-card">
            <span style="font-size: 0.85rem; color: #aaa; text-transform: uppercase; letter-spacing: 1px;">Your Short Link</span>
            
            <div class="short-url-box" id="shortUrlDisplay">
                <!-- URL will appear here -->
            </div>

            <div class="action-buttons">
                <button onclick="copyToClipboard()" id="copyBtn" class="btn-secondary">
                    <span>Copy</span>
                </button>
                <a href="#" id="visitBtn" target="_blank" class="btn-secondary" style="text-decoration: none; display: flex; align-items: center; justify-content: center;">
                    Visit Link
                </a>
            </div>
        </div>
    </div>

    <footer>
        &copy; 2026 Minimalist Shortener
    </footer>

    <script src="script.js"></script>
</body>
</html>
