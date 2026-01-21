<?php
require_once 'db.php';

$alias = $_GET['alias'] ?? '';

if (empty($alias)) {
    header("Location: index.php");
    exit;
}

try {
    // Get URL from database
    $stmt = $conn->prepare("SELECT id, original_url, is_active FROM urls WHERE alias = :alias LIMIT 1");
    $stmt->execute([':alias' => $alias]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Check if URL is active
        if ($result['is_active'] != 1) {
            echo '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Link Deactivated</title>
                <style>
                    body {
                        font-family: "Inter", sans-serif;
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        min-height: 100vh;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin: 0;
                        padding: 20px;
                    }
                    .container {
                        background: white;
                        padding: 40px;
                        border-radius: 16px;
                        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                        text-align: center;
                        max-width: 500px;
                    }
                    h1 { color: #dc3545; margin-bottom: 15px; }
                    p { color: #666; margin-bottom: 25px; }
                    a {
                        display: inline-block;
                        padding: 12px 30px;
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        text-decoration: none;
                        border-radius: 8px;
                        font-weight: 600;
                        transition: transform 0.2s;
                    }
                    a:hover { transform: translateY(-2px); }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>⚠️ Link Deactivated</h1>
                    <p>This link has been deactivated by the owner.</p>
                    <a href="index.php">Go Home</a>
                </div>
            </body>
            </html>';
            exit;
        }

        $original_url = $result['original_url'];
        $url_id = $result['id'];
        
        // Update click counter
        $update = $conn->prepare("UPDATE urls SET clicks = clicks + 1, last_clicked_at = NOW() WHERE id = :id");
        $update->execute([':id' => $url_id]);
        
        // Track analytics (optional - detailed tracking)
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $referer = $_SERVER['HTTP_REFERER'] ?? null;
            
            $analyticsStmt = $conn->prepare("
                INSERT INTO click_analytics (url_id, ip_address, user_agent, referer) 
                VALUES (:url_id, :ip, :ua, :ref)
            ");
            $analyticsStmt->execute([
                ':url_id' => $url_id,
                ':ip' => $ip,
                ':ua' => $userAgent,
                ':ref' => $referer
            ]);
        } catch (PDOException $e) {
            // Ignore analytics errors, don't break redirect
        }
        
        // Redirect to original URL
        header("Location: " . $original_url, true, 301);
        exit;
        
    } else {
        // URL not found - Show 404 page
        http_response_code(404);
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>404 - Link Not Found</title>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                body {
                    font-family: "Inter", sans-serif;
                    background: #0f0f13;
                    color: white;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                    position: relative;
                    overflow: hidden;
                }
                .bg-mesh {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    z-index: -1;
                }
                .blob {
                    position: absolute;
                    filter: blur(80px);
                    opacity: 0.4;
                    animation: float 10s infinite ease-in-out;
                    border-radius: 50%;
                }
                .blob-1 {
                    top: -10%;
                    left: -10%;
                    width: 50vw;
                    height: 50vw;
                    background: #bd00ff;
                }
                .blob-2 {
                    bottom: -10%;
                    right: -10%;
                    width: 50vw;
                    height: 50vw;
                    background: #00f7ff;
                }
                @keyframes float {
                    0%, 100% { transform: translate(0, 0) scale(1); }
                    33% { transform: translate(30px, -50px) scale(1.1); }
                    66% { transform: translate(-20px, 20px) scale(0.9); }
                }
                .container {
                    text-align: center;
                    max-width: 600px;
                    background: rgba(255, 255, 255, 0.03);
                    backdrop-filter: blur(16px);
                    border: 1px solid rgba(255, 255, 255, 0.08);
                    border-radius: 24px;
                    padding: 60px 40px;
                    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
                }
                .error-code {
                    font-size: 120px;
                    font-weight: 800;
                    background: linear-gradient(to right, #bd00ff, #00f7ff);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    line-height: 1;
                    margin-bottom: 20px;
                }
                h1 {
                    font-size: 2rem;
                    margin-bottom: 15px;
                    color: white;
                }
                p {
                    color: #aaa;
                    font-size: 1.1rem;
                    margin-bottom: 30px;
                    line-height: 1.6;
                }
                .btn {
                    display: inline-block;
                    padding: 14px 32px;
                    background: linear-gradient(135deg, #bd00ff, #ff00aa);
                    color: white;
                    text-decoration: none;
                    border-radius: 12px;
                    font-weight: 600;
                    font-size: 1rem;
                    transition: all 0.3s;
                }
                .btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 10px 30px rgba(189, 0, 255, 0.3);
                }
                @media (max-width: 768px) {
                    .error-code { font-size: 80px; }
                    h1 { font-size: 1.5rem; }
                    p { font-size: 1rem; }
                    .container { padding: 40px 30px; }
                }
            </style>
        </head>
        <body>
            <div class="bg-mesh">
                <div class="blob blob-1"></div>
                <div class="blob blob-2"></div>
            </div>
            
            <div class="container">
                <div class="error-code">404</div>
                <h1>Link Not Found</h1>
                <p>The link you are looking for does not exist or has been removed. Please check the URL and try again.</p>
                <a href="index.php" class="btn">Go Home</a>
            </div>
        </body>
        </html>';
        exit;
    }

} catch (PDOException $e) {
    // Database error
    http_response_code(500);
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error</title>
        <style>
            body {
                font-family: sans-serif;
                background: #0f0f13;
                color: white;
                text-align: center;
                padding: 50px 20px;
            }
            h1 { color: #ff4d4d; }
            a {
                color: #00f7ff;
                text-decoration: none;
            }
        </style>
    </head>
    <body>
        <h1>⚠️ Error Processing Request</h1>
        <p>An error occurred while processing your request.</p>
        <p><a href="index.php">Go Home</a></p>
    </body>
    </html>';
}
?>
