<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Check - URL Shortener</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .check-item {
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            background: #f8f9fa;
            border-left: 4px solid #ddd;
        }
        .check-item.success {
            background: #d4edda;
            border-left-color: #28a745;
        }
        .check-item.error {
            background: #f8d7da;
            border-left-color: #dc3545;
        }
        .check-item.warning {
            background: #fff3cd;
            border-left-color: #ffc107;
        }
        .icon {
            font-size: 24px;
            margin-right: 15px;
            min-width: 30px;
        }
        .check-content {
            flex: 1;
        }
        .check-title {
            font-weight: 600;
            margin-bottom: 5px;
        }
        .check-desc {
            font-size: 0.9rem;
            color: #666;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        .code-block {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            overflow-x: auto;
            margin: 10px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: transform 0.2s;
            margin-top: 20px;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 System Check</h1>
            <p>URL Shortener - Configuration Checker</p>
        </div>
        
        <div class="content">
            <?php
            $allGood = true;
            $errors = [];
            $warnings = [];
            
            // Check PHP Version
            echo '<div class="section">';
            echo '<h2>PHP Configuration</h2>';
            
            $phpVersion = phpversion();
            $phpOk = version_compare($phpVersion, '7.4.0', '>=');
            echo '<div class="check-item ' . ($phpOk ? 'success' : 'error') . '">';
            echo '<div class="icon">' . ($phpOk ? '✅' : '❌') . '</div>';
            echo '<div class="check-content">';
            echo '<div class="check-title">PHP Version: ' . $phpVersion . '</div>';
            echo '<div class="check-desc">' . ($phpOk ? 'PHP version is compatible' : 'PHP 7.4 or higher required') . '</div>';
            echo '</div></div>';
            if (!$phpOk) $allGood = false;
            
            // Check PDO
            $pdoLoaded = extension_loaded('pdo');
            echo '<div class="check-item ' . ($pdoLoaded ? 'success' : 'error') . '">';
            echo '<div class="icon">' . ($pdoLoaded ? '✅' : '❌') . '</div>';
            echo '<div class="check-content">';
            echo '<div class="check-title">PDO Extension</div>';
            echo '<div class="check-desc">' . ($pdoLoaded ? 'PDO is enabled' : 'PDO extension is not loaded') . '</div>';
            echo '</div></div>';
            if (!$pdoLoaded) {
                $allGood = false;
                $errors[] = 'PDO extension not loaded';
            }
            
            // Check PDO MySQL
            $pdoMysqlLoaded = extension_loaded('pdo_mysql');
            echo '<div class="check-item ' . ($pdoMysqlLoaded ? 'success' : 'error') . '">';
            echo '<div class="icon">' . ($pdoMysqlLoaded ? '✅' : '❌') . '</div>';
            echo '<div class="check-content">';
            echo '<div class="check-title">PDO MySQL Driver</div>';
            echo '<div class="check-desc">' . ($pdoMysqlLoaded ? 'PDO MySQL driver is enabled' : 'PDO MySQL driver is NOT enabled - THIS IS THE PROBLEM!') . '</div>';
            echo '</div></div>';
            if (!$pdoMysqlLoaded) {
                $allGood = false;
                $errors[] = 'PDO MySQL driver not loaded';
            }
            
            // Check other useful extensions
            $mbstringLoaded = extension_loaded('mbstring');
            echo '<div class="check-item ' . ($mbstringLoaded ? 'success' : 'warning') . '">';
            echo '<div class="icon">' . ($mbstringLoaded ? '✅' : '⚠️') . '</div>';
            echo '<div class="check-content">';
            echo '<div class="check-title">Mbstring Extension</div>';
            echo '<div class="check-desc">' . ($mbstringLoaded ? 'Mbstring is enabled' : 'Mbstring is recommended but not critical') . '</div>';
            echo '</div></div>';
            
            echo '</div>';
            
            // Check Files
            echo '<div class="section">';
            echo '<h2>Required Files</h2>';
            
            $requiredFiles = [
                'index.php' => 'Homepage',
                'db.php' => 'Database configuration',
                'api.php' => 'API endpoint',
                'redirect.php' => 'URL redirector',
                'style.css' => 'Stylesheet',
                'script.js' => 'JavaScript',
                '.htaccess' => 'URL rewriting rules'
            ];
            
            foreach ($requiredFiles as $file => $desc) {
                $exists = file_exists($file);
                echo '<div class="check-item ' . ($exists ? 'success' : 'error') . '">';
                echo '<div class="icon">' . ($exists ? '✅' : '❌') . '</div>';
                echo '<div class="check-content">';
                echo '<div class="check-title">' . $file . '</div>';
                echo '<div class="check-desc">' . $desc . ' - ' . ($exists ? 'Found' : 'Missing') . '</div>';
                echo '</div></div>';
                if (!$exists) $allGood = false;
            }
            
            echo '</div>';
            
            // PHP.ini location
            echo '<div class="section">';
            echo '<h2>PHP Configuration File</h2>';
            echo '<div class="info-box">';
            echo '<strong>php.ini location:</strong><br>';
            echo '<code>' . php_ini_loaded_file() . '</code>';
            echo '</div>';
            echo '</div>';
            
            // Fix Instructions
            if (!$pdoMysqlLoaded) {
                echo '<div class="section">';
                echo '<h2>🔧 How to Fix PDO MySQL Driver</h2>';
                echo '<div class="check-item error">';
                echo '<div class="check-content">';
                echo '<div class="check-title">Step 1: Open php.ini</div>';
                echo '<div class="check-desc">Open this file in a text editor:</div>';
                echo '<div class="code-block">' . php_ini_loaded_file() . '</div>';
                echo '</div></div>';
                
                echo '<div class="check-item error">';
                echo '<div class="check-content">';
                echo '<div class="check-title">Step 2: Find and Uncomment</div>';
                echo '<div class="check-desc">Search for this line:</div>';
                echo '<div class="code-block">;extension=pdo_mysql</div>';
                echo '<div class="check-desc">Remove the semicolon to make it:</div>';
                echo '<div class="code-block">extension=pdo_mysql</div>';
                echo '</div></div>';
                
                echo '<div class="check-item error">';
                echo '<div class="check-content">';
                echo '<div class="check-title">Step 3: Restart Apache</div>';
                echo '<div class="check-desc">Go to XAMPP Control Panel and click "Stop" then "Start" for Apache</div>';
                echo '</div></div>';
                
                echo '<div class="check-item error">';
                echo '<div class="check-content">';
                echo '<div class="check-title">Step 4: Refresh This Page</div>';
                echo '<div class="check-desc">After restarting Apache, refresh this page to verify the fix</div>';
                echo '</div></div>';
                
                echo '</div>';
            }
            
            // Summary
            echo '<div class="section">';
            echo '<h2>Summary</h2>';
            if ($allGood) {
                echo '<div class="check-item success">';
                echo '<div class="icon">🎉</div>';
                echo '<div class="check-content">';
                echo '<div class="check-title">All Systems Go!</div>';
                echo '<div class="check-desc">Your system is properly configured. You can now use the URL Shortener.</div>';
                echo '</div></div>';
                echo '<a href="index.php" class="btn">Go to Homepage →</a>';
            } else {
                echo '<div class="check-item error">';
                echo '<div class="icon">⚠️</div>';
                echo '<div class="check-content">';
                echo '<div class="check-title">Configuration Issues Found</div>';
                echo '<div class="check-desc">Please fix the issues above before using the application.</div>';
                echo '</div></div>';
            }
            echo '</div>';
            
            // Loaded Extensions
            echo '<div class="section">';
            echo '<h2>All Loaded PHP Extensions</h2>';
            echo '<div class="code-block">';
            $extensions = get_loaded_extensions();
            sort($extensions);
            echo implode(', ', $extensions);
            echo '</div>';
            echo '</div>';
            ?>
        </div>
    </div>
</body>
</html>
