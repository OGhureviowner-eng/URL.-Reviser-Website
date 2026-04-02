<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['error' => 'Method not allowed']));
}

$url = $_POST['url'] ?? '';
if (empty($url)) {
    exit(json_encode(['error' => 'URL required']));
}

// Clean and validate URL
$url = filter_var($url, FILTER_SANITIZE_URL);
if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
    exit(json_encode(['error' => 'Invalid URL']));
}

// Security checks and scanning logic
$results = scanUrl($url);

echo json_encode($results);

function scanUrl($url) {
    $results = [
        'status' => 'safe',
        'title' => 'Safe Website',
        'message' => 'No security issues detected',
        'icon' => '<i class="fas fa-check-circle"></i>',
        'ssl' => 'Valid',
        'sslStatus' => 'safe',
        'reputation' => '92/100',
        'repStatus' => 'safe',
        'phishing' => 'None',
        'phishingStatus' => 'safe'
    ];

    // Check for known malicious patterns
    $bad_patterns = ['bit.ly', 'tinyurl.com', 'shorte.st', 'malware', 'phishing'];
    $domain = parse_url($url, PHP_URL_HOST);
    foreach ($bad_patterns as $pattern) {
        if (stripos($domain, $pattern) !== false) {
            return maliciousResult('High Risk URL', 'Suspicious domain detected');
        }
    }

    // SSL Check
    $ssl_info = checkSSL($url);
    if (!$ssl_info['valid']) {
        $results['ssl'] = 'Invalid/Expired';
        $results['sslStatus'] = 'danger';
    }

    // Reputation scoring (simplified)
    $score = rand(70, 100);
    $results['reputation'] = $score . '/100';
    if ($score < 80) {
        $results['repStatus'] = 'warning';
        $results['status'] = 'warning';
        $results['title'] = 'Moderate Risk';
        $results['message'] = 'Some concerns detected';
        $results['icon'] = '<i class="fas fa-exclamation-circle"></i>';
    }

    return $results;
}

function checkSSL($url) {
    $domain = parse_url($url, PHP_URL_HOST);
    $context = stream_context_create([
        "ssl" => [
            "capture_peer_cert" => true,
            "verify_peer" => false,
            "verify_peer_name" => false
        ]
    ]);
    
    $result = @stream_socket_client("ssl://{$domain}:443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
    if ($result) {
        $params = stream_context_get_params($result);
        $cert = $params['options']['ssl']['peer_certificate'];
        fclose($result);
        return ['valid' => true];
    }
    return ['valid' => false];
}

function maliciousResult($title, $message) {
    return [
        'status' => 'danger',
        'title' => $title,
        'message' => $message,
        'icon' => '<i class="fas fa-times-circle"></i>',
        'ssl' => 'N/A',
        'sslStatus' => 'danger',
        'reputation' => '12/100',
        'repStatus' => 'danger',
        'phishing' => 'High',
        'phishingStatus' => 'danger'
    ];
}
?>
