<?php
header('Content-Type: application/json');
require_once 'db.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$url = trim($input['url'] ?? '');

// Basic Validation
if (empty($url)) {
    http_response_code(400);
    echo json_encode(['error' => 'URL cannot be empty']);
    exit;
}


$parsed = parse_url($url);
if (empty($parsed['scheme'])) {
    $url = 'https://' . $url;
}


if (!filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid URL format']);
    exit;
}


function generateAlias($length = 6) {
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $alias = '';
    for ($i = 0; $i < $length; $i++) {
        $alias .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $alias;
}

$alias = generateAlias();
$max_retries = 5;
$retry_count = 0;


// Loop to ensure uniqueness (collision check)
while ($retry_count < $max_retries) {
    try {
        $userId = $_SESSION['user_id'] ?? null;
        $stmt = $conn->prepare("INSERT INTO urls (alias, original_url, user_id) VALUES (:alias, :url, :uid)");
        $stmt->execute([':alias' => $alias, ':url' => $url, ':uid' => $userId]);
        
        // Success
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domain = $_SERVER['HTTP_HOST'];
        
        $path = dirname($_SERVER['PHP_SELF']);
        $path = $path == '/' || $path == '\\' ? '' : $path;
        
        
        $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); 
        if ($dir == '/') $dir = '';
        
        $shortUrl = $protocol . $domain . $dir . '/' . $alias;

        echo json_encode([
            'success' => true, 
            'alias' => $alias,
            'shortUrl' => $shortUrl
        ]);
        exit;
        
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry
            $alias = generateAlias(); 
            $retry_count++;
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Database error']);
            exit;
        }
    }
}

http_response_code(500);
echo json_encode(['error' => 'Failed to generate unique alias after multiple retries']);
?>
